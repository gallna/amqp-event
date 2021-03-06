<?php
namespace Kemer\Amqp\Addons;

use Kemer\Amqp;

class AddonsEvent extends Amqp\AmqpEvent
{
    const QUEUE_GET_COMMAND = 'command.kemer.queue.get';
    const DEAD_LETTER = 'kemer.deadLetter';
    const POSTPONE = 'kemer.postpone';
    const ERROR = 'kemer.error';
}
