<?php
namespace Kemer\Amqp;

class ConsumeEvent extends AmqpEvent
{
    /**
     * @var AMQPQueue
     */
    public $queue;

    /**
     * @param string $message
     */
    public function __construct(\AMQPEnvelope $envelope, \AMQPQueue $queue)
    {
        $this->envelope = $envelope;
        $this->queue = $queue;
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
