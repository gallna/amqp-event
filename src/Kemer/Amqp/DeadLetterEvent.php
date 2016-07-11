<?php
namespace Kemer\Amqp;

class DeadLetterEvent extends ConsumeEvent
{
    const SEND = "dead.letter.send";

    /**
     * @var \Exception
     */
    public $exception;

    /**
     * @param AMQPEnvelope $envelope
     * @param AMQPQueue $queue
     */
    public function __construct(\AMQPEnvelope $envelope, \AMQPQueue $queue, \Exception $exception = null)
    {
        parent::__construct($envelope, $queue);
        $this->exception = $exception;
    }

    /**
     * Returns Exception triggered this event
     *
     * @return Exception|null
     */
    public function getException()
    {
        return $this->exception;
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
        $headers["x-exception"] = [
            "message" => $this->getException() ?? $this->getException()->getMessage(),
            "code" => $this->getException() ?? $this->getException()->getCode(),
        ];

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
