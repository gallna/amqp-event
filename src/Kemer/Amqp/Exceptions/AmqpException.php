<?php
namespace Kemer\Amqp\Exceptions;

use Kemer\Amqp\ConsumeEvent;

interface AmqpException
{
    /**
     * Returns AmqpEvent which trigger exception
     *
     * @return AmqpEvent
     */
    public function getEvent();
}
