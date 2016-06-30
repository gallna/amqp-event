<?php
namespace Kemer\Amqp;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

class Consumer implements ConsumerInterface
{
    /**
     * @var ConsumerInterface
     */
    protected $consumer;

    /**
     * @var broker
     */
    protected $broker;

    /**
     * @param ConsumerInterface $consumer
     */
    public function __construct(ConsumerInterface $consumer, $broker)
    {
        $this->consumer = $consumer;
        $this->broker = $broker;
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
        try {
            $this->consumer->__invoke($envelope, $queue);
            $event->ack();
        } catch (Exceptions\DelayException $e) {
            echo $e->getMessage();
            $this->delay($envelope, $queue);
            var_dump($envelope);
            $event->ack();
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
