<?php
namespace Kemer\Amqp;

use Symfony\Component\EventDispatcher\Event;

/*
AMQPEnvelope::getAppId — Get the message appid
AMQPEnvelope::getBody — Get the message body
AMQPEnvelope::getContentEncoding — Get the message contentencoding
AMQPEnvelope::getContentType — Get the message contenttype
AMQPEnvelope::getCorrelationId — Get the message correlation id
AMQPEnvelope::getDeliveryTag — Get the message delivery tag
AMQPEnvelope::getExchange — Get the message exchange
AMQPEnvelope::getExpiration — Get the message expiration
AMQPEnvelope::getHeader — Get a specific message header
AMQPEnvelope::getHeaders — Get the message headers
AMQPEnvelope::getMessageId — Get the message id
AMQPEnvelope::getPriority — Get the message priority
AMQPEnvelope::getReplyTo — Get the message replyto
AMQPEnvelope::getRoutingKey — Get the message routing key
AMQPEnvelope::getTimeStamp — Get the message timestamp
AMQPEnvelope::getType — Get the message type
AMQPEnvelope::getUserId — Get the message user id
AMQPEnvelope::isRedelivery — Whether this is a redelivery of the message
*/
class AmqpEvent extends Event
{
    public $envelope;

    /**
     * @var string
     */
    public $message;

    /**
     * @param string $message
     */
    public function __construct(string $message, \AMQPEnvelope $envelope = null)
    {
        $this->message = $message;
        $this->envelope = $envelope;
    }

    /**
     * Returns message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Handle dynamic calls.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return $this
     */
    public function __call($method, $parameters)
    {
        return $this->envelope->{$method}(...$parameters);
    }
}
