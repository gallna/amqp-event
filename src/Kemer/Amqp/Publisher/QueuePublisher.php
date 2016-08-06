<?php
namespace Kemer\Amqp\Publisher;

use Symfony\Component\EventDispatcher\Event;
use Kemer\Amqp;

class QueuePublisher extends AbstractPublisher
{
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
            $exchange = $this->broker->exchange();
            return $this->publish($event, $eventName, $exchange);
        }
    }
}
