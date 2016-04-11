<?php
namespace Kemer\Amqp;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

class Dispatcher extends EventDispatcher
{
    /**
     * @var broker
     */
    protected $broker;

    /**
     * Constructor
     *
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct($broker)
    {
        $this->broker = $broker;

    }

    /**
     * {@inheritdoc}
     */
    public function dispatch($eventName, Event $event = null)
    {
        parent::dispatch($eventName, $event);
        if ($event instanceof AmqpEvent) {
            $this->broker->publish($eventName, $event->getMessage());
        }
    }
}
