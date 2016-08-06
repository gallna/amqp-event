<?php
namespace App;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Kemer\Amqp\AmqpEvent;
use Kemer\Amqp\Exceptions;

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
            'kernel.critical' => 'onKernelCritical',
            'kernel.notice' => 'onKernelNotice',
            'kernel.info' => 'onKernelInfo',
            'kernel.wait' => 'onKernelWait',
        ];
    }

    private function display(AmqpEvent $event, $eventName, $methodName, $color = "1;32")
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

    public function onKernelCritical(AmqpEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        $this->display($event, $eventName, __METHOD__);
        $event->ack();
    }

    public function onKernelError(AmqpEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        $this->display($event, $eventName, __METHOD__);
        throw new \LogicException("Dead-letter exception");
        $event->ack();
    }

    public function onKernelWarning(AmqpEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        $this->display($event, $eventName, __METHOD__);
        $event->ack();
    }

    public function onKernelNotice(AmqpEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        $this->display($event, $eventName, __METHOD__);
        $event->ack();
    }

    public function onKernelInfo(AmqpEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        $this->display($event, $eventName, __METHOD__, 34);
        $event->ack();
    }

    public function onKernelWait(AmqpEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        $this->display($event, $eventName, __METHOD__, 31);
        $event->ack();
    }
}
