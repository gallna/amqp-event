<?php
namespace Kemer\Amqp\Exceptions;

use Kemer\Amqp\AmqpEvent;

trait AmqpExceptionTrait
{
    private $event;

    public static function fromException(\Exception $e, AmqpEvent $event)
    {
        $exception = new static($e->getMessage(), $e->getCode(), $e);
        $exception->setEvent($event);
        return $exception;
    }


    /**
     * AmqpEvent setter
     *
     * @param AmqpEvent
     */
    public function setEvent(AmqpEvent $event)
    {
        $this->event = $event;
    }

    /**
     * AmqpEvent getter
     *
     * @return AmqpEvent
     */
    public function getEvent()
    {
        return $this->event;
    }
}
