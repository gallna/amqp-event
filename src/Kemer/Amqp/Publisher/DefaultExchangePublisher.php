<?php
namespace Kemer\Amqp\Publisher;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Kemer\Amqp\Broker;
use Kemer\Amqp;

class DefaultExchangePublisher extends AbstractPublisher
{
    /**
     * @var AMQPExchange
     */
    protected $defaultExchange;

    /**
     * @param Broker $broker
     * @param AMQPExchange $defaultExchange
     * @param bool $stopPropagation
     */
    public function __construct(\AMQPExchange $defaultExchange, ...$arguments)
    {
        parent::__construct(...$arguments);
        $this->defaultExchange = $defaultExchange;
    }

    /**
     * Returns default exchange
     *
     * @return AMQPExchange
     */
    protected function getDefaultExchange()
    {
        return $this->defaultExchange;
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
    public function onPublish(Event $event, $eventName)
    {
        if ($event instanceof Amqp\PublishEvent) {
            $exchange = $this->getDefaultExchange();
            return $this->publish($event, $eventName, $exchange);
        }
    }
}
