<?php
namespace Kemer\Amqp\Exceptions;

use Kemer\Amqp\ConsumeEvent;

class ConsumerException extends \RuntimeException implements AmqpException
{
    use AmqpExceptionTrait;

    public function __construct(ConsumeEvent $event, \Exception $e)
    {
        parent::__construct($e->getMessage(), $e->getCode(), $e);
        $this->setEvent($event);
    }
}
