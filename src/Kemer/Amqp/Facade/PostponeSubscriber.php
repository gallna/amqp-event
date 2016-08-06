<?php
namespace Kemer\Amqp\Facade;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Kemer\Amqp\Broker;
use Kemer\Amqp;

class PostponeSubscriber extends Postpone implements EventSubscriberInterface
{
    /**
     * @var bool Whether no further event listeners should be triggered
     */
    private $stopPropagation = true;

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'kemer.postpone' => [
                ['onPostpone', 1001]
            ]
        ];
    }

    /**
     * @param Broker $broker
     * @param bool $stopPropagation
     */
    public function __construct(Broker $broker, $stopPropagation = true)
    {
        $this->broker = $broker;
        $this->stopPropagation = $stopPropagation;
    }

    /**
     * On dispatch event listener - called on any event
     *
     * @param Event $event
     * @param string $eventName
     * @return void
     */
    public function onPostpone(Event $event, $eventName)
    {
        if ($event instanceof Amqp\AmqpEvent) {
            if ($this->postpone($event)) {
                $event->ack();
            }
            if ($this->stopPropagation) {
                $event->stopPropagation();
            }
        }
    }

    /**
     * On dispatch event listener - called on any event
     *
     * @param Event $event
     * @param string $eventName
     * @return void
     */
    public function onException(Event $event)
    {
        if ($event instanceof Amqp\ErrorEvent) {
            $event->getEvent()->addHeader("x-exception", [
                "message" => $event->getException()->getMessage(),
                "code" => $event->getException()->getCode(),
                "class" => get_class($event->getException()),
            ]);
            $this->postpone($event->getEvent());
            $event->getEvent()->ack();
            if ($this->stopPropagation) {
                $event->stopPropagation();
            }
        }
    }
}
