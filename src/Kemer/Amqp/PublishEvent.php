<?php
namespace Kemer\Amqp;

class PublishEvent extends AmqpEvent
{
    /**
     * @var AMQPEnvelope
     */
    public $envelope;

    /**
     * @param Envelope $envelope
     */
    public function __construct(Envelope $envelope = null)
    {
        $this->envelope = $envelope ?: new Envelope();
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
}
