<?php
namespace Kemer\Amqp;

/**
 * stub class representing AMQPEnvelope from pecl-amqp
 */
trait BasicPropertiesTrait
{
    private $appId;
    private $contentEncoding;
    private $contentType;
    private $correlationId;
    private $deliveryMode;
    private $expiration;
    private $priority;
    private $replyTo;
    private $type;
    private $userId;
    private $messageId;
    private $timestamp;
    private $headers = [];
    private $body;

    // Moved to PublishEvent
    // private $exchangeName;
    // private $routingKey;
    // Moved to ConsumeEvent
    // private $deliveryTag;
    // private $consumerTag;
    // private $redelivery;

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
     * Get the delivery mode of the message. (persistent or not)
     * Messages may be published as persistent, which makes the AMQP broker persist them to disk
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
     * @param string $key Name of the header to get the value from.
     * @return string|boolean The contents of the specified header or FALSE
     *                        if not set.
     */
    public function getHeader($key, $default = null)
    {
        return $this->hasHeader($key) ? $this->headers[$key] : $default;
    }

    /**
     * Check whether specific message header exists.
     *
     * @param string $key Name of the header to check.
     *
     * @return boolean
     */
    public function hasHeader($key)
    {
      return isset($this->headers[$key]);
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
     * Set message headers.
     *
     * @param of key value pairs associated with the message.
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Add message header.
     *
     * @param $key key to add
     */
    public function addHeader($key, $header)
    {
        $this->headers[$key] = $header;
        return $this;
    }

    /**
     * Remove header by key.
     *
     * @param $key key to remove
     */
    public function removeHeader($key)
    {
        if ($this->hasHeader($key)) {
            unset($this->headers[$key]);
        }
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
     * Get the timestamp of the message.
     *
     * @return string The message timestamp.
     */
    public function getTimeStamp()
    {
        return $this->timestamp;
    }

    /**
     * Get the timestamp of the message.
     *
     * @param The message timestamp.
     */
    public function setTimeStamp($timestamp)
    {
        $this->timestamp = $timestamp;
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
     * Returns basic attributes as an array
     * Shortcut to jsonSerialize()
     *
     * @return array
     */
    public function attributes()
    {
        return $this->jsonSerialize();
    }

    /**
     * {@inheritdoc} JsonSerializable
     */
    public function jsonSerialize()
    {
        return array_filter([
            "app_id" => $this->getAppId(),
            "user_id" => $this->getUserId(),
            "message_id" => $this->getMessageId(),
            "priority" => $this->getPriority(),
            "type" => $this->getType(),
            "reply_to" => $this->getReplyTo(),
            "timestamp" => $this->getTimeStamp(),
            "delivery_mode" => $this->getDeliveryMode(),
            "content_type" => $this->getContentType(),
            "content-encoding" => $this->getContentEncoding(),
            "expiration" => $this->getExpiration(),
            "correlation-id" => $this->getCorrelationId(),
            "headers" => $this->getHeaders(),
        ]);
    }
}


