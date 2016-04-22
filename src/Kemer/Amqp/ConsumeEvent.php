<?php
namespace Kemer\Amqp;

class ConsumeEvent extends AmqpEvent
{
    /**
     * @var AMQPEnvelope
     */
    public $envelope;

    /**
     * @var AMQPQueue
     */
    public $queue;

    /**
     * @param AMQPEnvelope $envelope
     * @param AMQPQueue $queue
     */
    public function __construct(\AMQPEnvelope $envelope, \AMQPQueue $queue)
    {
        $this->envelope = $envelope;
        $this->queue = $queue;
    }

    /**
     * Returns AMQP envelope
     *
     * @return AMQPEnvelope
     */
    public function getEnvelope()
    {
        return $this->envelope;
    }

    /**
     * Returns AMQP queue
     *
     * @return AMQPQueue
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * This method allows the acknowledgement of a message that is retrieved without
     * the AMQP_AUTOACK flag through AMQPQueue::get() or AMQPQueue::consume()
     *
     * @return bool
     */
    public function ack()
    {
        return $this->queue->ack($this->envelope->getDeliveryTag());
    }

    /**
     * Mark a message as explicitly not acknowledged.
     * When called, the broker will immediately put the message back onto the queue, instead of waiting until the connection is closed.
     *
     * @return bool
     */
    public function nack()
    {
        return $this->queue->nack($this->envelope->getDeliveryTag());
    }
}
