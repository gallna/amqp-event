<?php
namespace Kemer\Amqp;

interface ConsumerInterface
{
    /**
     * Dispatch message events
     *
     * @param AMQPEnvelope $envelope
     * @param AMQPQueue $queue
     * @return void
     */
    public function __invoke(\AMQPEnvelope $envelope, \AMQPQueue $queue);
}
