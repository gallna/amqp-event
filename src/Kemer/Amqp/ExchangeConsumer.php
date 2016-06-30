<?php
namespace Kemer\Amqp\Consumer;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Kemer\Amqp\Exchange\ExchangeName;

class ExchangeConsumer extends Consumer
{
    /**
     * @var AMQPChannel
     */
    protected $channel;

    /**
     * @var AMQPExchange
     */
    protected $exchange;

    /**
     * @param AMQPChannel $exchange
     * @param AMQPExchange $exchange
     */
    public function __construct(\AMQPChannel $channel, \AMQPExchange $exchange)
    {
        $this->channel = $channel;
        $this->exchange = $exchange;
    }

    /**
     * Run the Amqp listener
     *
     * @return void
     */
    public function listen($flags = null)
    {
        $queue = new \AMQPQueue($this->channel());
        $queue->setFlags(AMQP_AUTODELETE);
        $queue->declareQueue();
        $events = array_keys($this->getDispatcher()->getListeners());
        foreach ($events as $eventName) {
            $queue->bind($this->exchange->getName(), $eventName);
        }
        parent::listen($queue, $flags)
    }
}
