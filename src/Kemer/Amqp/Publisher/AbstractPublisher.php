<?php
namespace Kemer\Amqp\Publisher;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Kemer\Amqp;

abstract class AbstractPublisher
{
    /**
     * @var Broker
     */
    protected $broker;

    /**
     * @var bool Whether no further event listeners should be triggered
     */
    private $stopPropagation = true;

    /**
     * @param Amqp\Broker $broker
     * @param AMQPExchange $defaultExchange
     * @param bool $stopPropagation
     */
    public function __construct(Amqp\Broker $broker, $stopPropagation = true)
    {
        $this->broker = $broker;
        $this->stopPropagation = $stopPropagation;
    }

    abstract public function onPublish(Event $event, $eventName);

    /**
     * {@inheritDoc}
     */
    public function __invoke(Event $event, $eventName, EventDispatcher $dispatcher)
    {
        return $this->onPublish($event, $eventName, $dispatcher);
    }

    /**
     * On dispatch event listener - called on any event
     * AMQP_MANDATORY: When publishing a message, the message must be routed to a valid queue. If it is not, an error will be returned.
     *  if the client publishes a message with the "mandatory" flag set to an exchange of "direct" type which is not bound to a queue.
     * AMQP_IMMEDIATE: When publishing a message, mark this message for immediate processing by the broker.
     *      REMOVED from rabbitmq > 3-0 http://www.rabbitmq.com/blog/2012/11/19/breaking-things-with-rabbitmq-3-0
     * @param Event $event
     * @param string $eventName
     * @return void
     */
    public function publish(Amqp\PublishEvent $event, $eventName, \AMQPExchange $exchange)
    {
        $success = $exchange->publish(
            $event->getBody(),
            $eventName,
            $event->getFlags(),
            $event->jsonSerialize()
        );
        if ($this->stopPropagation) {
            $event->stopPropagation();
        }
    }
}
