<?php
namespace App;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Kemer\Amqp\AmqpEvent;

class Listener
{
    private function display(Event $event, $eventName, $methodName, $color = "32")
    {
        sprintf(
            "\033[%sm %s@%s\033[0m\033[36m \033[0m %s \n",
            $color,
            get_class($event),
            $eventName,
            $methodName
        );
    }

    public function onKernel(AmqpEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        $this->display($event, $eventName, __METHOD__);
    }

    public function onAll(Event $event, $eventName, EventDispatcher $dispatcher)
    {
        $this->display($event, $eventName, __METHOD__);
    }

    public function onMessage(Event $event, $eventName, EventDispatcher $dispatcher)
    {
        $this->display($event, $eventName, __METHOD__, 34);
        $event->ack();
    }
}
