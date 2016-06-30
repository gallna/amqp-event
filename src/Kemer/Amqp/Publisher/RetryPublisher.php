<?php
namespace Kemer\Amqp\Publisher;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;

class RetryPublisher implements EventSubscriberInterface
{
    /**
     * @var AMQPChannel
     */
    protected $channel;

    /**
     * @var AMQPExchange
     */
    protected $defaultExchange;

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
                ['onDispatch', 1000]
            ]
        ];
    }

    /**
     * @param AMQPChannel $channel
     * @param AMQPExchange $defaultExchange
     * @param bool $stopPropagation
     */
    public function __construct(\AMQPChannel $channel, \AMQPExchange $defaultExchange = null, $stopPropagation = true)
    {
        $this->channel = $channel;
        $this->defaultExchange = $defaultExchange;
        $this->stopPropagation = $stopPropagation;
    }

    /**
     * Creates if needed and returns default exchange
     *
     * @return AMQPExchange
     */
    protected function getDefaultExchange()
    {
        if (!$this->defaultExchange) {
            $this->defaultExchange = new \AMQPExchange($this->channel);
        }
        return $this->defaultExchange;
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
        if ($event instanceof RetryEvent) {
            $this->delay($event->getEnvelope(), $event->getQueue());

            if ($this->stopPropagation) {
                $event->stopPropagation();
            }
            //var_dump($event, $exchange->getType()); die;
        }
    }


    public function delay($envelope, $queue, $expiration = 10000)
    {
        $queue->getName();
        $waitQueue = new \AMQPQueue($this->broker->channel());
        $waitQueue->setName($envelope->getExchangeName()."-wait");
        $waitQueue->setArgument("x-dead-letter-exchange", $envelope->getExchangeName());
        $waitQueue->setFlags(AMQP_PASSIVE);
        $waitQueue->declareQueue();
        $this->dispatch($envelope, $waitQueue, $expiration);
    }

    public function dispatch($envelope, $waitQueue, $expiration = 6000)
    {
        $headers = $envelope->getHeaders();
        $headers["x-retry-count"] = isset($headers["x-retry-count"])
            ? $headers["x-retry-count"] + 1
            : 0;
        $attributes = [
            "app_id" => $envelope->getAppId(),
            "user_id" => $envelope->getUserId(),
            "message_id" => $envelope->getMessageId(),
            "priority" => $envelope->getPriority(),
            "type" => $envelope->getType(),
            "reply_to" => $envelope->getReplyTo(),
            "timestamp" => $envelope->getTimeStamp(),
            "delivery_mode" => $envelope->getDeliveryMode(),
            "content_type" => $envelope->getContentType(),
            "expiration" => $expiration,
            "headers" => $headers,
        ];
        $exchange = new \AMQPExchange($this->broker->channel());
        $exchange->setName($envelope->getExchangeName()."-wait");
        $exchange->setType(AMQP_EX_TYPE_DIRECT);
        $exchange->setFlags(AMQP_PASSIVE);
        $exchange->declare();
        $waitQueue->bind($envelope->getExchangeName()."-wait", $envelope->getRoutingKey());
        $exchange->publish(
            $envelope->getBody(),
            $envelope->getRoutingKey(),
            AMQP_NOPARAM,
            $attributes
        );
    }
}
