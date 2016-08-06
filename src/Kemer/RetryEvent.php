<?php
namespace Kemer\Amqp;

class RetryEvent extends Event
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
     * @var ConsumeEvent
     */
    public $event;

    /**
     * @param ConsumeEvent $event
     * @param integer $expiration
     * @param integer $retryCount
     */
    public function __construct(ConsumeEvent $event, $expiration, $retryCount)
    {
        $this->event = $event;
        $this->expiration = $expiration;
        $this->retryCount = $retryCount;
    }

    /**
     * Returns Exception triggered this event
     *
     * @return ConsumeEvent
     */
    public function getConsumeEvent()
    {
        return $this->event;
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
        $headers = $this->event->getHeaders();
        $headers["x-retry-count"] = isset($headers["x-retry-count"])
            ? --$headers["x-retry-count"]
            : $this->getRetryCount();
        return [
            "app_id" => $this->event->getAppId(),
            "user_id" => $this->event->getUserId(),
            "message_id" => $this->event->getMessageId(),
            "priority" => $this->event->getPriority(),
            "type" => $this->event->getType(),
            "reply_to" => $this->event->getReplyTo(),
            "timestamp" => $this->event->getTimeStamp(),
            "delivery_mode" => $this->event->getDeliveryMode(),
            "content_type" => $this->event->getContentType(),
            "expiration" => $this->getExpiration(),
            "headers" => $headers,
        ];
    }
}
