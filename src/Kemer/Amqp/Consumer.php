<?php
namespace Kemer\Amqp;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

class Consumer implements ConsumerInterface
{
    /**
     * @var DispatcherInterface
     */
    protected $dispatcher;

    /**
     * @param DispatcherInterface $dispatcher
     */
    public function __construct(DispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Run the Amqp listener
     *
     * @return void
     */
    public function listen(\AMQPQueue $queue, $flags = null)
    {
        $queue->consume($this, $flags);
    }

    /**
     * Dispatch message events
     *
     * @param AMQPEnvelope $envelope
     * @param AMQPQueue $queue
     * @return void
     */
    public function __invoke(\AMQPEnvelope $envelope, \AMQPQueue $queue)
    {
        $event = new ConsumeEvent($envelope, $queue);
        try {
            $this->dispatcher->dispatch($envelope->getRoutingKey(), $event);
            $event->ack();
        } catch (Exceptions\DelayException $e) {
            echo $e->getMessage();
            $retryEvent = new RetryEvent($envelope, $queue, $e->getExpiration(), $e->getRetryCount());
            $this->getDispatcher()->dispatch(RetryEvent::RETRY, $retryEvent);
            $event->ack();
        }
    }
}
