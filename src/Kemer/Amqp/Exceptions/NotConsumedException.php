<?php
namespace Kemer\Amqp\Exceptions;

use Kemer\Amqp\ConsumeEvent;

class NotConsumedException extends \DomainException implements AmqpException
{
    use AmqpExceptionTrait;

    public function __construct(ConsumeEvent $event)
    {
        parent::__construct("Unknown routing-key: {$event->getRoutingKey()}");
        $this->setEvent($event);
    }
}
