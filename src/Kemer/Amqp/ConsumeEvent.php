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
     * @var bool
     */
    private $consumed = false;

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
        $this->consumed = true;
        return $this->queue->ack($this->envelope->getDeliveryTag());
    }

    /**
     * Mark a message as explicitly not acknowledged.
     * The AMQP specification defines the basic.reject method that allows clients
     * to reject individual, delivered messages, instructing the broker
     * to either discard them or requeue them.
     *
     * @param integer $flags AMQP_REQUEUE to requeue the message(s),
     * @return bool
     */
    public function reject($flags = AMQP_NOPARAM)
    {
        $this->consumed = true;
        return $this->queue->reject($this->envelope->getDeliveryTag(), $flags);
    }

    /**
     * Mark a message as explicitly not acknowledged.
     * RabbitMQ supports the nack method that provides all the functionality of
     * basic.reject whilst also allowing for bulk processing of messages.
     * To reject messages in bulk, clients set the multiple flag of the basic.nack method
     * to true. The broker will then reject all unacknowledged, delivered messages up to
     * and including the message specified in the delivery_tag field of the
     * basic.nack method. In this respect, basic.nack complements the bulk acknowledgement
     * semantics of basic.ack.
     *
     * @param integer $flags AMQP_MULTIPLE to nack all previous unacked messages as well.
     *                       AMQP_REQUEUE to requeue the message(s),
     *
     * @return bool
     */
    public function nack($flags = AMQP_NOPARAM)
    {
        $this->consumed = true;
        return $this->queue->nack($this->envelope->getDeliveryTag(), $flags);
    }

    /**
     * Allows to see if event was processed by listener
     *
     * @return bool
     */
    public function isConsumed()
    {
        return $this->consumed;
    }

    public function retry($expiration = 10000, $retryCount = null)
    {
        $this->getDispatcher()->dispatch(
            RetryEvent::RETRY,
            new RetryEvent($this->getEnvelope(), $this->getQueue(), $expiration, $retryCount)
        );
        $this->ack();
    }
}
