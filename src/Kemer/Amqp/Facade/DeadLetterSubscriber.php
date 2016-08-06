<?php
namespace Kemer\Amqp\Facade;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Kemer\Amqp\Broker;
use Kemer\Amqp;

class DeadLetterSubscriber extends DeadLetter implements EventSubscriberInterface
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
            'kemer.dead-letter' => [
                ['onDeadLetter', 1001]
            ],
            'LogicException' => [
                ['onException', 1001]
            ],
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
    public function onDeadLetter(Event $event, $eventName)
    {
        if ($event instanceof Amqp\AmqpEvent) {
            $this->deadLetter($event);
            $event->reject(null);
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
    public function onException(Event $event, $eventName)
    {
        if ($event instanceof Amqp\ErrorEvent) {
            $event->getEvent()->addHeader("x-exception", [
                "message" => $event->getException()->getMessage(),
                "code" => $event->getException()->getCode(),
                "class" => get_class($event->getException()),
            ]);
            $this->deadLetter($event->getEvent(), $event->getException());
            $event->getEvent()->reject(null);
            if ($this->stopPropagation) {
                $event->stopPropagation();
            }
        }
    }
}
