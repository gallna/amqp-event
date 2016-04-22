<?php
namespace Kemer\Amqp\Exchange;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Kemer\Amqp\PublishEvent;

class Subscriber implements EventSubscriberInterface
{
    /**
     * @var AMQPExchange
     */
    protected $exchange;

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            '#' => 'onDispatch'
        ];
    }

    /**
     * @param AMQPExchange $exchange
     */
    public function __construct(\AMQPExchange $exchange)
    {
        $this->exchange = $exchange;
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
            //$event->envelope->setRoutingKey($eventName);
            //$event->envelope->setExchangeName($this->exchangeName);
            //$event->envelope->setType($this->getExchangeType($event));
            $attributes = [
                "content_type" => $event->getContentType(),
            ];
            $this->exchange->publish(
                $event->getBody(),
                $eventName,
                AMQP_NOPARAM,
                $attributes
            );
        }
    }
}
