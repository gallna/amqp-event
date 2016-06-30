<?php
namespace Kemer\Amqp\Publisher;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;

class DefaultPublisher implements EventSubscriberInterface
{
    /**
     * @var AMQPChannel
     */
    protected $channel;

    /**
     * @var AMQPExchange
     */
    protected $defaultExchange;

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
            '#' => [
                ['onDispatch', 1000]
            ]
        ];
    }

    /**
     * @param AMQPChannel $channel
     * @param AMQPExchange $defaultExchange
     * @param bool $stopPropagation
     */
    public function __construct(\AMQPChannel $channel, \AMQPExchange $defaultExchange = null, $stopPropagation = true)
    {
        $this->channel = $channel;
        $this->defaultExchange = $defaultExchange;
        $this->stopPropagation = $stopPropagation;
    }

    /**
     * Creates if needed and returns default exchange
     *
     * @return AMQPExchange
     */
    protected function getDefaultExchange()
    {
        if (!$this->defaultExchange) {
            $this->defaultExchange = new \AMQPExchange($this->channel);
        }
        return $this->defaultExchange;
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
            if ($event->getEnvelope()->getExchangeName() === null) {
                $exchange = $this->getDefaultExchange();
            } else {
                $exchange = new \AMQPExchange($this->channel);
                $exchange->setName($event->getEnvelope()->getExchangeName());
            }

            $attributes = [
                "content_type" => $event->getContentType(),
                "expiration" => $event->getExpiration(),
            ];

            $exchange->publish(
                $event->getBody(),
                $eventName,
                AMQP_NOPARAM,
                $attributes
            );

            if ($this->stopPropagation) {
                $event->stopPropagation();
            }
            //var_dump($event, $exchange->getType()); die;
        }
    }
}
