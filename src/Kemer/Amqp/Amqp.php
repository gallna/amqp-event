<?php
namespace Kemer\Amqp;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

class Amqp
{
    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var broker
     */
    protected $broker;

    /**
     * Constructor
     *
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct($broker, EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->broker = $broker;
    }

    /**
     * Get event dispatcher
     *
     * @return EventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * Returns broker
     *
     * @return
     */
    public function getBroker()
    {
        return $this->broker;
    }

    /**
     * Run the Amqp event
     *
     * @return void
     */
    public function run()
    {
        $events = array_map(
            [$this->getBroker(), "subscribe"],
            array_keys($this->getEventDispatcher()->getListeners())
        );
        $this->getBroker()->run([$this, 'onMessage']);
    }

    /**
     * Dispatch message events
     *
     * @param string $message
     * @return void
     */
    public function onMessage(AmqpEvent $event)
    {
        $dispatcher = $this->getEventDispatcher();
        $events = array_map( function ($listener) use ($dispatcher, $event) {
            $pattern = str_replace("*", "([\w*]+)", $listener);
            if ($listener == $event->getName() || preg_match("~$pattern~", $event->getName())) {
                $dispatcher->dispatch($listener, $event);
            }
        }, array_keys($this->getEventDispatcher()->getListeners()));
        $dispatcher->dispatch("#", $event);
    }

    /**
     * Send response for current message
     *
     * @param SsdpEvent $event
     * @return void
     */
    public function publish($channel, $message)
    {
        $this->getBroker()->publish($channel, $message);
    }
}
