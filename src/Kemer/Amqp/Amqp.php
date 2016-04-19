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
     * @var string
     */
    protected $exchangeName;

    /**
     * Constructor
     *
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct($broker, $exchangeName, EventDispatcher $eventDispatcher = null)
    {
        $this->broker = $broker;
        $this->exchangeName = $exchangeName;
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
     * On dispatch event listener - called on any event
     *
     * @param Event $event
     * @param string $eventName
     * @return void
     */
    public function onDispatch(Event $event, $eventName)
    {
        if ($event instanceof PublishEvent) {
            $event->envelope->setRoutingKey($eventName);
            $event->envelope->setExchangeName($this->exchangeName);
            $this->getBroker()->publish($event);
        }
    }

    /**
     * Run the Amqp listener
     *
     * @return void
     */
    public function listen(array $events = [], $noack = false)
    {
        $events = $events ?: array_keys($this->getEventDispatcher()->getListeners());
        foreach ($events as $eventName) {
            $this->getBroker()->subscribe($this->exchangeName, $eventName);
        }
        $this->getBroker()->queue()
            ->consume(
                [$this, 'onMessage'],
                $noack ? AMQP_NOACK : AMQP_NOPARAM
            );
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
            new ConsumeEvent(
                $envelope,
                $this->getBroker()->queue()
            )
        );
    }
}
