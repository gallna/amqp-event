<?php
namespace Kemer\Amqp\Publisher;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Kemer\Amqp\Broker;
use Kemer\Amqp;

class RetryPublisher implements EventSubscriberInterface
{
    /**
     * @var AMQPChannel
     */
    protected $channel;

    /**
     * @var bool Whether no further event listeners should be triggered
     */
    private $stopPropagation = true;

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            '#' => [
                ['onDispatch', 1001]
            ]
        ];
    }

    /**
     * @param Broker $broker
     * @param bool $stopPropagation
     */
    public function __construct(Broker $broker, $stopPropagation = true)
    {
        $this->broker = $broker;
        $this->stopPropagation = $stopPropagation;
    }

    /**
     * On dispatch event listener - called on any event
     *
     * @param Event $event
     * @param string $eventName
     * @return void
     */
    public function onDispatch(Event $event, $eventName)
    {
        if ($event instanceof Amqp\RetryEvent) {
            if (!$event->expired()) {
                $envelope = $event->getEnvelope();
                $waitQueue = $this->getQueue($envelope);
                $waitExchange = $this->getExchange($envelope);
                $waitQueue->bind($waitExchange->getName(), $event->getRoutingKey());
                $waitExchange->publish(
                    $event->getBody(),
                    $event->getRoutingKey(),
                    AMQP_NOPARAM,
                    $event->attributes()
                );
            }
            $event->ack();
            if ($this->stopPropagation) {
                $event->stopPropagation();
            }
        }
    }

    /**
     * Creates wait queue
     *
     * @return AMQPQueue
     */
    private function getQueue(\AMQPEnvelope $envelope)
    {
        $waitQueue = new \AMQPQueue($this->broker->channel());
        $waitQueue->setName($envelope->getExchangeName()."-wait");
        $waitQueue->setArgument("x-dead-letter-exchange", $envelope->getExchangeName());
        $waitQueue->setFlags(AMQP_DURABLE);
        $waitQueue->declareQueue();
        return $waitQueue;
    }

    /**
     * Creates wait exchange
     *
     * @return AMQPExchange
     */
    private function getExchange(\AMQPEnvelope $envelope)
    {
        $exchange = new \AMQPExchange($this->broker->channel());
        $exchange->setName($envelope->getExchangeName()."-wait");
        $exchange->setType(AMQP_EX_TYPE_DIRECT);
        $exchange->setFlags(AMQP_DURABLE);
        $exchange->declare();
        return $exchange;
    }
}
