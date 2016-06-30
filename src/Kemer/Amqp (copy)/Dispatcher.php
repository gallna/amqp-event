<?php
namespace Kemer\Amqp;

use Symfony\Component\EventDispatcher\EventDispatcher;

class Dispatcher extends EventDispatcher
{
    /**
     * {@inheritdoc}
     */
    public function getListeners($eventName = null)
    {
        if ($eventName === null) {
            return parent::getListeners($eventName);
        }
        $listeners = [];
        foreach (parent::getListeners() as $name => $eventListeners) {
            $namePattern = str_replace(["*", "#"], ["(\w+)", "([\w\.]+)"], $name);
            if (preg_match("~^$namePattern$~", $eventName)) {
                $listeners = array_merge($listeners, $eventListeners);
            }
        }
        return $listeners;
    }

    /**
     * {@inheritdoc}
     */
    public function addListener($event, $listener, $priority = 0)
    {
        if (is_string($event)) {
            return parent::addListener($event, $listener, $priority);
        }
        if ($event instanceof \AMQPExchange) {
            $this->consumeExchange($event, $listener);
        }
        if ($event instanceof \AMQPQueue) {
            $this->addConsumer($event, $listener);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function consumeExchange(\AMQPExchange $exchange, callable $consumer, $flags = null)
    {
        $queue = new \AMQPQueue($this->channel());
        $queue->setFlags(AMQP_AUTODELETE);
        $queue->declareQueue();
        $queue->bind($exchange->getName(), "#");
        $this->addConsumer($queue, $consumer, $flags);
    }

    /**
     * {@inheritdoc}
     */
    public function addConsumer(\AMQPQueue $queue, callable $consumer, $flags = null)
    {
        $queue->consume($consumer, $flags);
    }
}
