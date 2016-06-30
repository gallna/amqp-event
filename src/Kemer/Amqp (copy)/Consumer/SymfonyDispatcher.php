<?php
namespace Kemer\Amqp\Consumer;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Kemer\Amqp\ConsumerInterface;

class SymfonyDispatcher implements ConsumerInterface
{
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @param Dispatcher $dispatcher
     */
    public function __construct(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Dispatch message events
     *
     * @param string $eventName
     * @param ConsumeEvent $event
     * @return void
     */
    public function __invoke(\AMQPEnvelope $envelope, \AMQPQueue $queue)
    {
        $this->dispatcher->dispatch(
            $envelope->getRoutingKey(),
            $event = new ConsumeEvent($envelope, $queue)
        );
    }
}
