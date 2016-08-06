<?php
namespace Kemer\Amqp;

use Symfony\Component\EventDispatcher\GenericEvent as SymfonyEvent;

class Consumer
{
    const ERROR_EVENT = 'kemer.error';

    /**
     * @var DispatcherInterface
     */
    private $dispatcher;

    /**
     * Specifies the identifier for the consumer. The consumer tag is local to
     * a channel, so two clients can use the same consumer tags. If this field
     * is empty the server will generate a unique tag
     *
     * @var string
     */
    private $consumerTag;

    /**
     * @param DispatcherInterface $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Get event dispatcher
     *
     * @return dispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * Specifies the identifier for the consumer.
     *
     * @return string The consumer tag.
     */
    public function getConsumerTag()
    {
        return $this->consumerTag;
    }

    /**
     * Get the consumer tag of the message.
     *
     * @param message consumer tag.
     */
    public function setConsumerTag($consumerTag)
    {
        $this->consumerTag = $consumerTag;
        return $this;
    }

    /**
     * Bind queue to exchange using dispatcher event names as routing keys
     *
     * @return void
     * @throws AMQPExchangeException
     */
    public function bind(\AMQPQueue $queue, \AMQPExchange $exchange)
    {
        $events = array_keys($this->getDispatcher()->getListeners());
        foreach ($events as $eventName) {
            $queue->bind($exchange->getName(), $eventName);
        }
    }

    /**
     * Run queue consumer. Blocking function that will retrieve next messages from the queue
     * as it becomes available and will pass it off to the __invoke method.
     * Bind given queue to to provided exchange on all used events as routing keys.
     *
     * @param AMQPQueue $queue
     * @param AMQPExchange|null $exchange
     * @param integer $flags AMQP_AUTOACK the messages will be immediately marked as acknowledged by the server upon delivery.
     * @return void
     */
    public function listen(\AMQPQueue $queue, \AMQPExchange $exchange = null, $flags = AMQP_NOPARAM)
    {
        $exchange and $this->bind($queue, $exchange);
        $queue->consume($this, $flags);
    }

    /**
     * A callback function to which the consumed message will be passed.
     * All messages are dispatched as events.
     * Catched errors are dispatched as well using special event "kemer.error".
     * NotConsumedException is threw - when dispatched message is not marked
     * as `consumed` either by acknowledging or rejecting
     *
     * @param AMQPEnvelope $envelope
     * @param AMQPQueue $queue
     * @return void
     * @throws Exceptions\ConsumerException
     * @throws Exceptions\NotConsumedException
     */
    public function __invoke(\AMQPEnvelope $envelope, \AMQPQueue $queue)
    {
        $event = new ConsumeEvent($envelope, $queue);
        try {
            $this->dispatcher->dispatch($event->getRoutingKey(), $event);
        } catch (\Exception $e) {
            $this->dispatcher
                ->dispatch(static::ERROR_EVENT, new SymfonyEvent($event, ["error" => $e]));
            if (!$event->isConsumed()) {
                throw new Exceptions\ConsumerException($event, $e);
            }
        }
        if (!$event->isConsumed()) {
            throw new Exceptions\NotConsumedException($event);
        }
    }
}
