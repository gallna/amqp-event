<?php
namespace Kemer\Amqp;

/**
 * stub class representing AMQPEnvelope from pecl-amqp
 */
class Envelope
{
    private $appId;
    private $body;
    private $contentEncoding;
    private $contentType;
    private $correlationId;
    private $deliveryMode;
    private $deliveryTag;
    private $exchangeName;
    private $expiration;
    private $headers;
    private $priority;
    private $replyTo;
    private $routingKey;
    private $type;
    private $userId;
    private $redelivery;

    public static function fromEnvelope(\AMQPEnvelope $envelope)
    {
        $self = new static();
        $self->setAppId($envelope->getAppId());
        $self->setBody($envelope->getBody());
        $self->setContentEncoding($envelope->getContentEncoding());
        $self->setContentType($envelope->getContentType());
        $self->setCorrelationId($envelope->getCorrelationId());
        $self->setDeliveryMode($envelope->getDeliveryMode());
        $self->setDeliveryTag($envelope->getDeliveryTag());
        $self->setExchangeName($envelope->getExchangeName());
        $self->setExpiration($envelope->getExpiration());
        $self->setHeaders($envelope->getHeaders());
        $self->setMessageId($envelope->getMessageId());
        $self->setPriority($envelope->getPriority());
        $self->setReplyTo($envelope->getReplyTo());
        $self->setRoutingKey($envelope->getRoutingKey());
        $self->setTimeStamp($envelope->getTimeStamp());
        $self->setType($envelope->getType());
        $self->setUserId($envelope->getUserId());
        $self->setRedelivery($envelope->isRedelivery());
        return $self;
    }

    /**
     * Get the application id of the message.
     *
     * @return string The application id of the message.
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * Get the application id of the message.
     *
     * @param id of the message.
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
        return $this;
    }

    /**
     * Get the body of the message.
     *
     * @return string The contents of the message body.
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Get the body of the message.
     *
     * @param string The contents of the message body.
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Get the content encoding of the message.
     *
     * @return string The content encoding of the message.
     */
    public function getContentEncoding()
    {
        return $this->contentEncoding;
    }

    /**
     * Get the content encoding of the message.
     *
     * @param encoding of the message.
     */
    public function setContentEncoding($contentEncoding)
    {
        $this->contentEncoding = $contentEncoding;
        return $this;
    }

    /**
     * Get the message content type.
     *
     * @return string The content type of the message.
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Get the message content type.
     *
     * @param The content type of the message.
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * Get the message correlation id.
     *
     * @return string The correlation id of the message.
     */
    public function getCorrelationId()
    {
        return $this->correlationId;
    }

    /**
     * Get the message correlation id.
     *
     * @param string The correlation id of the message.
     */
    public function setCorrelationId($correlationId)
    {
        $this->correlationId = $correlationId;
        return $this;
    }

    /**
     * Get the delivery mode of the message.
     *
     * @return integer The delivery mode of the message.
     */
    public function getDeliveryMode()
    {
        return $this->deliveryMode;
    }

    /**
     * Get the delivery mode of the message.
     *
     * @param delivery mode of the message.
     */
    public function setDeliveryMode($deliveryMode)
    {
        $this->deliveryMode = $deliveryMode;
        return $this;
    }

    /**
     * Get the delivery tag of the message.
     *
     * @return string The delivery tag of the message.
     */
    public function getDeliveryTag()
    {
        return $this->deliveryTag;
    }

    /**
     * Get the delivery tag of the message.
     *
     * @param tag of the message.
     */
    public function setDeliveryTag($deliveryTag)
    {
        $this->deliveryTag = $deliveryTag;
        return $this;
    }

    /**
     * Get the exchange name on which the message was published.
     *
     * @return string The exchange name on which the message was published.
     */
    public function getExchangeName()
    {
        return $this->exchangeName;
    }

    /**
     * Get the exchange name on which the message was published.
     *
     * @param the message was published.
     */
    public function setExchangeName($exchangeName)
    {
        $this->exchangeName = $exchangeName;
        return $this;
    }

    /**
     * Get the expiration of the message.
     *
     * @return string The message expiration.
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * Get the expiration of the message.
     *
     * @param The message expiration.
     */
    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;
        return $this;
    }

    /**
     * Get a specific message header.
     *
     * @param string $header_key Name of the header to get the value from.
     *
     * @return string|boolean The contents of the specified header or FALSE
     *                        if not set.
     */
    public function getHeader($header_key)
    {
        return $this->headers[$header_key];
    }

    /**
     * Check whether specific message header exists.
     *
     * @param string $header_key Name of the header to check.
     *
     * @return boolean
     */
    public function hasHeader($header_key)
    {
      return isset($this->headers[$header_key]);
    }

    /**
     * Get the headers of the message.
     *
     * @return array An array of key value pairs associated with the message.
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get the headers of the message.
     *
     * @param of key value pairs associated with the message.
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Get the message id of the message.
     *
     * @return string The message id
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * Get the message id of the message.
     *
     * @param The message id
     */
    public function setMessageId($messageId)
    {
        $this->messageId = $messageId;
        return $this;
    }

    /**
     * Get the priority of the message.
     *
     * @return int The message priority.
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Get the priority of the message.
     *
     * @param The message priority.
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * Get the reply-to address of the message.
     *
     * @return string The contents of the reply to field.
     */
    public function getReplyTo()
    {
        return $this->replyTo;
    }

    /**
     * Get the reply-to address of the message.
     *
     * @param of the reply to field.
     */
    public function setReplyTo($replyTo)
    {
        $this->replyTo = $replyTo;
        return $this;
    }

    /**
     * Get the routing key of the message.
     *
     * @return string The message routing key.
     */
    public function getRoutingKey()
    {
        return $this->routingKey;
    }

    /**
     * Get the routing key of the message.
     *
     * @param The message routing key.
     */
    public function setRoutingKey($routingKey)
    {
        $this->routingKey = $routingKey;
        return $this;
    }

    /**
     * Get the timestamp of the message.
     *
     * @return string The message timestamp.
     */
    public function getTimeStamp()
    {
        return $this->timeStamp;
    }

    /**
     * Get the timestamp of the message.
     *
     * @param The message timestamp.
     */
    public function setTimeStamp($timeStamp)
    {
        $this->timeStamp = $timeStamp;
        return $this;
    }

    /**
     * Get the message type.
     *
     * @return string The message type.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get the message type.
     *
     * @param string The message type.
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get the message user id.
     *
     * @return string The message user id.
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Get the message user id.
     *
     * @param string The message user id.
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * Whether this is a redelivery of the message.
     *
     * Whether this is a redelivery of a message. If this message has been
     * delivered and AMQPEnvelope::nack() was called, the message will be put
     * back on the queue to be redelivered, at which point the message will
     * always return TRUE when this method is called.
     *
     * @return bool TRUE if this is a redelivery, FALSE otherwise.
     */
    public function isRedelivery()
    {
        return $this->redelivery;
    }

    /**
     * Whether this is a redelivery of the message.
     *
     * @param bool TRUE if this is a redelivery, FALSE otherwise.
     */
    public function setRedelivery($redelivery)
    {
        $this->redelivery = $redelivery;
        return $this;
    }
}


