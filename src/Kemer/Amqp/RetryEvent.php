<?php
namespace Kemer\Amqp;

class RetryEvent extends ConsumeEvent
{
    const RETRY = "retry";

    /**
     * @var integer
     */
    public $expiration;

    /**
     * @var integer
     */
    public $retryCount;

    /**
     * @param AMQPEnvelope $envelope
     * @param AMQPQueue $queue
     */
    public function __construct(\AMQPEnvelope $envelope, \AMQPQueue $queue, $expiration, $retryCount)
    {
        parent::__construct($envelope, $queue);
        $this->expiration = $expiration;
        $this->retryCount = $retryCount;
    }

    /**
     * Returns message expiration time
     *
     * @return integer
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * Returns message retry count
     *
     * @return integer
     */
    public function getRetryCount()
    {
        return $this->retryCount;
    }

    /**
     * Checks if message was expired or retried too many times
     *
     * @return bool
     */
    public function expired()
    {
        if ($this->getRetryCount() > 0) {
            return $this->attributes()["headers"]["x-retry-count"] <= 0;
        }
        return false;
    }

    /**
     * Returns attributes send to wait exchange
     *
     * @return array
     */
    public function attributes()
    {
        $envelope = $this->getEnvelope();
        $headers = $envelope->getHeaders();
        $headers["x-retry-count"] = isset($headers["x-retry-count"])
            ? --$headers["x-retry-count"]
            : $this->getRetryCount();
        return [
            "app_id" => $envelope->getAppId(),
            "user_id" => $envelope->getUserId(),
            "message_id" => $envelope->getMessageId(),
            "priority" => $envelope->getPriority(),
            "type" => $envelope->getType(),
            "reply_to" => $envelope->getReplyTo(),
            "timestamp" => $envelope->getTimeStamp(),
            "delivery_mode" => $envelope->getDeliveryMode(),
            "content_type" => $envelope->getContentType(),
            "expiration" => $this->getExpiration(),
            "headers" => $headers,
        ];
    }
}
