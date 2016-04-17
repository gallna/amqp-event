<?php
namespace App;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Kemer\Amqp\AmqpEvent;

class Subscriber implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'kernel.error' => 'onKernelError',
            'kernel.warning' => 'onKernelWarning',

        ];
    }

    public function onKernelError(AmqpEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        echo sprintf(
            "%s (%s) [%s]: %s\n",
            __METHOD__,
            $eventName,
            $event->getRoutingKey(),
            $event->getMessage()
        );
    }

    public function onKernelWarning(AmqpEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        echo sprintf(
            "%s (%s) [%s]: %s\n",
            __METHOD__,
            $eventName,
            $event->getRoutingKey(),
            $event->getMessage()
        );
    }
}