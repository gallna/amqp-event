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
     * Returns AMQP envelope
     *
     * @return AMQPEnvelope
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * Returns AMQP queue
     *
     * @return AMQPQueue
     */
    public function getRetryCount()
    {
        return $this->retryCount;
    }
}
