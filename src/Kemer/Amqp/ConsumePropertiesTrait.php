<?php
namespace Kemer\Amqp;

/**
 * stub class representing AMQPEnvelope from pecl-amqp
 */
trait ConsumePropertiesTrait
{
    use BasicPropertiesTrait;

    /**
     * This indicates that the message has been previously delivered to this or another client.
     *
     * @var string
     */
    private $redelivered;

    /**
     * The server-assigned and channel-specific delivery tag
     *
     * @var sting
     */
    private $deliveryTag;

    /**
     * Specifies the routing key name specified when the message was published.
     *
     * @var string
     */
    private $routingKey;

    /**
     * Specifies the name of the exchange that the message was originally
     * published to. May be empty, meaning the default exchange.
     *
     * @var string
     */
    private $exchangeName;

    public function fromEnvelope(\AMQPEnvelope $envelope)
    {
        $this->setAppId($envelope->getAppId());
        $this->setBody($envelope->getBody());
        $this->setContentEncoding($envelope->getContentEncoding());
        $this->setContentType($envelope->getContentType());
        $this->setCorrelationId($envelope->getCorrelationId());
        $this->setDeliveryMode($envelope->getDeliveryMode());
        $this->setDeliveryTag($envelope->getDeliveryTag());
        $this->setExchangeName($envelope->getExchangeName());
        $this->setExpiration($envelope->getExpiration());
        $this->setHeaders($envelope->getHeaders());
        $this->setMessageId($envelope->getMessageId());
        $this->setPriority($envelope->getPriority());
        $this->setReplyTo($envelope->getReplyTo());
        $this->setRoutingKey($envelope->getRoutingKey());
        $this->setTimeStamp($envelope->getTimeStamp());
        $this->setType($envelope->getType());
        $this->setUserId($envelope->getUserId());
        $this->setRedelivery($envelope->isRedelivery());
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
     * Get the exchange name on which the message was published.
     *
     * @return string The exchange name on which the message was published.
     */
    public function getExchangeName()
    {
        return $this->exchangeName;
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
}
