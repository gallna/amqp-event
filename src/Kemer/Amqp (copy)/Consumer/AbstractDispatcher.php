<?php
namespace Kemer\Amqp\Consumer;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Kemer\Amqp\Exchange\ExchangeName;

abstract class AbstractDispatcher
{
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var broker
     */
    protected $broker;

    /**
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher, $broker)
    {
        $this->dispatcher = $dispatcher;
        $this->broker = $broker;
    }

    /**
     * Get event dispatcher
     *
     * @return EventDispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * Dispatch message events
     *
     * @param AMQPEnvelope $envelope
     * @param AMQPQueue $queue
     * @return void
     */
    public function dispatch(ConsumeEvent $event)
    {
        $this->getDispatcher()->dispatch($event->getRoutingKey(), $event);
    }
}
