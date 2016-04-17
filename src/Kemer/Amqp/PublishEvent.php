<?php
namespace Kemer\Amqp;

class PublishEvent extends AmqpEvent
{
    /**
     * @param Envelope $message
     */
    public function __construct(Envelope $envelope = null)
    {
        $this->envelope = $envelope ?: new Envelope();
    }
}
