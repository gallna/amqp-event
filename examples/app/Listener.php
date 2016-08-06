<?php
namespace App;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Kemer\Amqp\AmqpEvent;

class Listener
{
    private function display(AmqpEvent $event, $eventName, $methodName, $color = "32")
    {
        echo sprintf(
            "\033[%sm %s@%s\033[0m\033[36m [%s]\033[0m %s: %s \n",
            $color,
            $event->getExchangeName(),
            $event->getRoutingKey(),
            $eventName,
            $methodName,
            $event->getBody()
        );
    }

    public function onKernel(AmqpEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        $this->display($event, $eventName, __METHOD__);
        $event->ack();
    }

    public function onAll(AmqpEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        $this->display($event, $eventName, __METHOD__);
        $event->ack();
    }

    public function onMessage(AmqpEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        $this->display($event, $eventName, __METHOD__, 34);
        $event->ack();
    }
}
