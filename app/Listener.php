<?php
namespace App;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Kemer\Amqp\AmqpEvent;

class Listener
{

    public function onKernel(AmqpEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        echo sprintf(
            "%s (%s) [%s]: %s\n",
            __METHOD__,
            $eventName,
            $event->getRoutingKey(),
            $event->getBody()
        );
    }

    public function onAll(AmqpEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        echo sprintf(
            "%s (%s) [%s]: %s\n",
            __METHOD__,
            $eventName,
            $event->getRoutingKey(),
            $event->getBody()
        );
    }
}
