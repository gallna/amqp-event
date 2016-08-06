<?php
namespace Kemer\Amqp\Addons\Command;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Kemer\Amqp\Addons\AddonsEvent;
use Kemer\Amqp;

class QueueGetCommand implements EventSubscriberInterface
{
    /**
     * @var bool Whether no further event listeners should be triggered
     */
    private $stopPropagation = true;

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AddonsEvent::QUEUE_GET_COMMAND => [
                ['onRetry', 1000]
            ],
        ];
    }

    /**
     * @param Amqp\Broker $broker
     * @param bool $stopPropagation
     */
    public function __construct(Amqp\Broker $broker, $stopPropagation = true)
    {
        $this->broker = $broker;
        $this->stopPropagation = $stopPropagation;
    }

    /**
     * On dispatch event listener - called on any event
     *
     * @param Event $event
     * @param string $eventName
     * @param EventDispatcher $dispatcher
     * @return void
     */
    public function onRetry(Amqp\AmqpEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        if ($ev = $this->getQueueEvent($event)) {
            try {
                $dispatcher->dispatch($ev->getRoutingKey(), $ev);
                if ($ev->isConsumed()) {
                    printf("%s@%s OK \n", $ev->getExchangeName(), $ev->getRoutingKey());
                    $ev->ack();
                }
            } catch (\Exception $e) {
                printf("%s@%s FAILED \n", $ev->getExchangeName(), $ev->getRoutingKey());
                $ev->reject();
            }
            if (!$ev->isConsumed()) {
                printf("%s@%s NOT CONSUMED \n", $ev->getExchangeName(), $ev->getRoutingKey());
                $ev->reject();
            }
            if ($this->stopPropagation) {
                $event->stopPropagation();
            }
            $event->ack();
        } else {
            printf("INVALID %s@%s \n", $ev->getExchangeName(), $ev->getRoutingKey());
        }
    }

    /**
     * Creates wait queue
     *
     * @return AMQPQueue
     */
    private function getQueueEvent(Amqp\AmqpEvent $event)
    {
        $properties = $event->getHeader("x-command-queue-get");
        if ($properties && ($flags = $properties["flags"]) && ($name = $properties["name"])) {
            $queue = $this->broker->queue($flags, $name);
            $envelope = $queue->get();
            return $envelope ? new Amqp\ConsumeEvent($envelope, $queue) : null;
        }
    }
}
