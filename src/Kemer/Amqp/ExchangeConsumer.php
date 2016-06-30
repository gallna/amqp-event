<?php
namespace Kemer\Amqp;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Kemer\Amqp\Exchange\ExchangeName;

class ExchangeConsumer
{
    /**
     * @var Broker
     */
    protected $broker;

    /**
     * @var Consumer
     */
    protected $consumer;

    /**
     * @param Consumer $consumer
     * @param Broker $broker
     */
    public function __construct(Consumer $consumer, Broker $broker)
    {
        $this->consumer = $consumer;
        $this->broker = $broker;
    }

    /**
     * Run the Amqp listener
     *
     * @return void
     */
    public function listen(\AMQPExchange $exchange, $flags = null)
    {
        $queue = new \AMQPQueue($this->broker->channel());
        $queue->setFlags(AMQP_AUTODELETE);
        $queue->declareQueue();
        $events = array_keys($this->consumer->getDispatcher()->getListeners());
        foreach ($events as $eventName) {
            $queue->bind($exchange->getName(), $eventName);
        }
        $this->consumer->listen($queue, $flags);
    }
}
