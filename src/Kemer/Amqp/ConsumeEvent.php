<?php
namespace Kemer\Amqp;

class ConsumeEvent extends AmqpEvent
{
    /**
     * @param string $message
     */
    public function __construct(\AMQPEnvelope $envelope)
    {
        $this->envelope = $envelope;
    }
}
