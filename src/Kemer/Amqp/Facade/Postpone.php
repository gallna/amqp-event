<?php
namespace Kemer\Amqp\Facade;

use Kemer\Amqp;

class Postpone
{
    /**
     * @var Broker
     */
    protected $broker;

    /**
     * @var integer
     */
    private $expiration = 5;

    /**
     * @var integer
     */
    private $retryCount = 3;

    /**
     * @param Broker $broker
     */
    public function __construct(Amqp\Broker $broker)
    {
        $this->broker = $broker;
        //$this->expiration = $expiration;
        //$this->retryCount = $retryCount
    }

    /**
     * Returns message expiration time
     *
     * @return integer
     */
    protected function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * Returns message retry count
     *
     * @return integer
     */
    protected function getRetryCount()
    {
        return $this->retryCount;
    }

    /**
     * On dispatch event listener - called on any event
     *
     * @param AmqpEvent $event
     * @return void
     */
    public function postpone(Amqp\AmqpEvent $event)
    {
        // Checks if message was expired or retried too many times
        if ($event->getHeader("x-retry-count", 0) < $this->getRetryCount()) {
            $event->addHeader("x-retry-count", $event->getHeader("x-retry-count", 0) + 1);
            $event->removeHeader("x-death");
            $exchange = $this->getExchange($event->getExchangeName(), $event->getRoutingKey());
            var_dump([
                "exchange" => $exchange->getName(),
                "routing-key" => $event->getRoutingKey(),
                "flags" => "AMQP_NOPARAM",
                "attributes" => $event->attributes()
            ]);
            $exchange->publish(
                $event->getBody(),
                $event->getRoutingKey(),
                AMQP_NOPARAM,
                $event->attributes()
            );
            return true;
        }
        return false;
    }

    /**
     * Creates wait exchange
     *
     * @return AMQPExchange
     */
    private function getExchange($name, $routingKey)
    {
        $exchange = $this->broker->declareExchange(null, $name."-wait", AMQP_EX_TYPE_TOPIC);
        $this->getQueue($name)->bind($exchange->getName(), $routingKey);
        return $exchange;
    }

    /**
     * Creates wait queue
     *
     * @return AMQPQueue
     */
    private function getQueue($name)
    {
        $queue = $this->broker->queue(null, $name."-wait");
        $queue->setArgument("x-dead-letter-exchange", $name);
        $queue->setArgument("x-message-ttl", $this->getExpiration() * 1000);
        $queue->declareQueue();
        return $queue;
    }
}
