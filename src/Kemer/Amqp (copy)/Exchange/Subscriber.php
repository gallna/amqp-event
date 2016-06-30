<?php
namespace Kemer\Amqp\Exchange;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Kemer\Amqp\PublishEvent;

class Subscriber implements EventSubscriberInterface
{
    /**
     * @var AMQPChannel
     */
    protected $channel;

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
     * @param AMQPChannel $channel
     */
    public function __construct(\AMQPChannel $channel)
    {
        $this->channel = $channel;
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
            $attributes = [
                "content_type" => $event->getContentType(),
                "expiration" => $event->getExpiration(),
            ];

            $exchange = new \AMQPExchange($this->channel);
            $exchange->setName($event->getEnvelope()->getExchangeName());
            $exchange->publish(
                $event->getBody(),
                $eventName,
                AMQP_NOPARAM,
                $attributes
            );
            //var_dump($event, $exchange->getType()); die;
        }
    }
}
