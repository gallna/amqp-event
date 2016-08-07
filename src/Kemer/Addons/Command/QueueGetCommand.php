<?php
namespace Kemer\Amqp\Addons\Command;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Monolog\Logger;
use Kemer\Amqp\Addons\AddonsEvent;
use Kemer\Amqp;

class QueueGetCommand implements EventSubscriberInterface
{
    /**
     * @var Monolog\Logger
     */
    private $logger;

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
     * @param Logger $logger
     * @param bool $stopPropagation
     */
    public function __construct(Amqp\Broker $broker, Logger $logger = null, $stopPropagation = true)
    {
        $this->broker = $broker;
        $this->logger = $logger;
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
            $this->log("INFO", "", $ev);
            try {
                $dispatcher->dispatch($ev->getRoutingKey(), $ev);
                if ($ev->isConsumed()) {
                    $ev->ack();
                }
            } catch (\Exception $e) {
                $this->log(
                    "ERROR", "FAILED", $ev,
                    [
                        "message" => $e->getMessage(),
                        "code" => $e->getCode(),
                        "class" => get_class($e)
                    ]
                );
                $ev->reject();
            }
            if (!$ev->isConsumed()) {
                $this->log("ERROR", "NOT CONSUMED", $ev);
                $ev->reject();
            }
            if ($this->stopPropagation) {
                $event->stopPropagation();
            }
            $event->ack();
        } else {
            $this->log("NOTICE", "INVALID", $event);
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

    /**
     * On dispatch event listener - called on any event
     *
     * @param Event $event
     * @param string $eventName
     * @return void
     */
    private function log($level, $message, $ev, array $context = [])
    {
        if ($this->logger) {
            $message = sprintf(
                "QueueGet \033[1;36m%s@%s\033[0m %s",
                $ev->getRoutingKey(),
                $ev->getExchangeName(),
                $message
            );
            $context["command"] = AddonsEvent::QUEUE_GET_COMMAND;
            $this->logger->log($level, $message, $context);
        }
    }
}
