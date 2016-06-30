<?php
include __DIR__.'/vendor/autoload.php';

use Kemer\Amqp;
use Kemer\Amqp\Broker\PhpAmqp;
use Kemer\Amqp\Broker\RabbitMQ;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

$amqp = new Amqp\Amqp(
    new Amqp\Broker\PhpAmqp(
        "rabbitmq.amqp.development.companycheck.co.uk", 5672, 'guest', 'guest'
    )
);

$envelope = new Amqp\Envelope();
$envelope->setExchangeName("exchangeName");
$envelope->setExpiration(10000);

$dispatcher = $amqp->getDispatcher();
// Publish some message
$dispatcher->dispatch("kernel.error", new Amqp\PublishEvent($envelope));
$dispatcher->dispatch("kernel.warning", new Amqp\PublishEvent($envelope));
$dispatcher->dispatch("kernel.critical", new Amqp\PublishEvent($envelope));
$dispatcher->dispatch("kernel.notice", new Amqp\PublishEvent($envelope));
$dispatcher->dispatch("kernel.info", new Amqp\PublishEvent($envelope));
$dispatcher->dispatch("kernel.info", new Event());
$dispatcher->dispatch("kernel.message", new Event());
