<?php
include __DIR__.'/vendor/autoload.php';

use Kemer\Amqp;
use Kemer\Amqp\Broker\PhpAmqp;
use Kemer\Amqp\Broker\RabbitMQ;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

$broker = new PhpAmqp('localhost', 5672, 'guest', 'guest');
$eventDispatcher = new Amqp\Dispatcher($broker);

// Publish some message
$eventDispatcher->dispatch("kernel.error", new Amqp\AmqpEvent("kernel error message"));
$eventDispatcher->dispatch("kernel.warning", new Amqp\AmqpEvent("kernel warning message"));
$eventDispatcher->dispatch("kernel.critical", new Amqp\AmqpEvent("kernel critical message"));
$eventDispatcher->dispatch("kernel.notice", new Amqp\AmqpEvent("kernel notice message"));
$eventDispatcher->dispatch("kernel.info", new Amqp\AmqpEvent("kernel info message"));
$eventDispatcher->dispatch("kernel.info", new Event());
$eventDispatcher->dispatch("kernel.message", new Event());
