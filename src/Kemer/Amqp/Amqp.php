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
    public function __construct($broker, EventDispatcher $eventDispatcher = null)
    {
        $this->broker = $broker;
        $this->eventDispatcher = $eventDispatcher ?: new Dispatcher();
        $this->eventDispatcher->addListener("#", [$this, "onDispatch"]);
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
     * Run the Amqp listener
     *
     * @return void
     */
    public function listen(array $events = [])
    {
        array_map(
            [$this->getBroker(), "subscribe"],
            $events ?: array_keys($this->getEventDispatcher()->getListeners())
        );
        $this->getBroker()->queue()->consume([$this, 'onMessage']);
    }

    /**
     * Dispatch message events
     *
     * @param string $message
     * @return void
     */
    public function onMessage(\AMQPEnvelope $envelope)
    {
        $this->getEventDispatcher()->dispatch(
            $envelope->getRoutingKey(),
            new ConsumeEvent($envelope)
        );
    }

    /**
     * Send response for current message
     *
     * @param Event $event
     * @param string $eventName
     * @return void
     */
    public function onDispatch(Event $event, $eventName)
    {
        if ($event instanceof PublishEvent) {
            $this->getBroker()->publish($eventName, $event);
        }
    }
}
