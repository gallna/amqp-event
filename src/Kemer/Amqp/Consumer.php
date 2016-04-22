<?php
namespace Kemer\Amqp;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

class Consumer
{
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Get event dispatcher
     *
     * @return EventDispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * Dispatch message events
     *
     * @param AMQPEnvelope $envelope
     * @param AMQPQueue $queue
     * @return void
     */
    public function __invoke(\AMQPEnvelope $envelope, \AMQPQueue $queue)
    {
        $this->getDispatcher()->dispatch(
            $envelope->getRoutingKey(),
            new ConsumeEvent($envelope, $queue)
        );
    }
}
