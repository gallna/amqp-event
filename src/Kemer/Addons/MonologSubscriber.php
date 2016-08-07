<?php
namespace Kemer\Amqp\Addons;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Monolog\Logger;
use Kemer\Amqp;

class MonologSubscriber implements EventSubscriberInterface
{
    /**
     * @var Monolog\Logger
     */
    private $logger;

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            // Amqp\Consumer::BIND_EVENT => 'onBind',
            "#" => [
                ['onAll', 1010]
            ],
            // Amqp\Consumer::ERROR_EVENT => [
            //     ['onError', -1010]
            // ]
        ];
    }

    /**
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * On dispatch event listener - called on any event
     *
     * @param Event $event
     * @param string $eventName
     * @return void
     */
    public function onBind(Event $event)
    {
        foreach ($event->getSubject() as $bound) {
            $this->logger->info(
                sprintf("Event '%s' bound to '%s@%s'", $bound, $event["exchange"], $event["queue"])
            );
        }
    }

    /**
     * On dispatch event listener - called on any event
     *
     * @param Event $event
     * @param string $eventName
     * @return void
     */
    public function onError(Event $event, $eventName)
    {
        $this->logger->error(
            sprintf(
                "Error (%s)'%s' | %s@%s | %s \n",
                $event["error"]->getCode(),
                $event["error"]->getMessage(),
                $event->getSubject()->getExchangeName(),
                $event->getSubject()->getRoutingKey(),
                $event->getSubject()->isRedelivery() ? "redelivery" : "new"
            ),
            [
                "message" => $event["error"]->getMessage(),
                "code" => $event["error"]->getCode(),
                "class" => get_class($event["error"]),
                "eventName" => $eventName
            ]
        );
    }

    /**
     * On dispatch event listener - called on any event
     *
     * @param Event $event
     * @param string $eventName
     * @return void
     */
    public function onAll(Event $event, $eventName)
    {
        switch (true) {
            case ($event instanceof Amqp\PublishEvent):
                $this->onPublish($event, $eventName);
                break;
            case ($event instanceof Amqp\ConsumeEvent):
                $this->onConsume($event, $eventName);
                break;
            case ($eventName == Amqp\Consumer::ERROR_EVENT):
                $this->onError($event, $eventName);
                break;
            case ($eventName == Amqp\Consumer::BIND_EVENT):
                $this->onBind($event, $eventName);
                break;
            default:
                $this->logger->info(
                    sprintf("Event \033[1;35m%s@%s\033[0m", $eventName, get_class($event)),
                    ["eventName" => $eventName]
                );

        }
    }

    /**
     * On dispatch event listener - called on any event
     *
     * @param Event $event
     * @param string $eventName
     * @return void
     */
    public function onConsume(Amqp\ConsumeEvent $event, $eventName)
    {
        $this->logger->info(
            sprintf("Consume event \033[1;36m%s@%s\033[0m", $eventName, $event->getExchangeName()),
            ["eventName" => $eventName]
        );
    }

    /**
     * On dispatch event listener - called on any event
     *
     * @param Event $event
     * @param string $eventName
     * @return void
     */
    public function onPublish(Amqp\PublishEvent $event, $eventName)
    {
        $this->logger->info(
            sprintf(
                "Publish event \033[1;32m%s%s\033[0m",
                $eventName,
                $event->getExchangeName() ? "@".$event->getExchangeName() : ""
            ),
            ["eventName" => $eventName]
        );
    }
}
