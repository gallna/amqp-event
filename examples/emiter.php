<?php
include __DIR__.'./../vendor/autoload.php';

use Kemer\Amqp;
use Symfony\Component\EventDispatcher\Event;

$broker = new Amqp\Broker(
    "localhost", 5672, 'guest', 'guest'
);

$dispatcher = new Amqp\Dispatcher();
$dispatcher->addSubscriber(new Amqp\Publisher\DefaultPublisher($broker));



// Publish message to queue
$envelope = new Amqp\Envelope();
$dispatcher->dispatch("queueName", new Amqp\PublishEvent($envelope));

// Publish message to exchange
$envelope = new Amqp\Envelope();
$envelope->setExchangeName("exchangeName");
$dispatcher->dispatch("kernel.critical", new Amqp\PublishEvent($envelope));
$dispatcher->dispatch("kernel.error", new Amqp\PublishEvent($envelope));
$dispatcher->dispatch("kernel.warning", new Amqp\PublishEvent($envelope));
$dispatcher->dispatch("kernel.notice", new Amqp\PublishEvent($envelope));

// Publish local messages
$dispatcher->dispatch("kernel.info", new Event());
$dispatcher->dispatch("kernel.message", new Event());

// Publish messages without direct listener
$dispatcher->dispatch("kernel.null", new Amqp\PublishEvent($envelope));

// Publish messages without any listener
$dispatcher->dispatch("critical.kernel", new Amqp\PublishEvent($envelope));

// Publish wait message
$dispatcher->dispatch("kernel.wait", new Amqp\PublishEvent($envelope));
