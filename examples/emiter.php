<?php
include __DIR__.'./../vendor/autoload.php';

use Kemer\Amqp;
use Symfony\Component\EventDispatcher\Event as SymfonyEvent;

$broker = new Amqp\Broker(
    "rabbit.docker", 5672, 'guest', 'guest'
);

$dispatcher = new Amqp\Dispatcher();
$dispatcher->addListener("#", new Amqp\Publisher\QueuePublisher($broker), 1000);
$dispatcher->addListener("#", new Amqp\Publisher\ExchangePublisher($broker), 1001);

// Publish message to queue
//$dispatcher->dispatch("some-exchange", new Amqp\PublishEvent());
//$dispatcher->dispatch("some-queue", new Amqp\PublishEvent());


// Publish queue-get command
$event = new Amqp\PublishEvent("queue-get");
$event->setExchangeName("some-exchange")
    ->setHeaders(["x-command-queue-get" => ["flags" => AMQP_DURABLE, "name" => "some-exchange-dlx"]]);

$dispatcher->dispatch('kemer.command.queue.get', $event);
return;
// Publish message to exchange
$dispatcher->dispatch(
    "kernel.error",
    (new Amqp\PublishEvent("error"))->setExchangeName("some-exchange")
);
return;
$dispatcher->dispatch(
    "kernel.wait",
    (new Amqp\PublishEvent("wait"))->setExchangeName("some-exchange")
);
$dispatcher->dispatch(
    "kernel.critical",
    (new Amqp\PublishEvent("critical"))->setExchangeName("some-exchange")
);
$dispatcher->dispatch(
    "kernel.error",
    (new Amqp\PublishEvent("error"))->setExchangeName("some-exchange")
);

$exchange = $broker->declareExchange(AMQP_DURABLE, "some-exchange", AMQP_EX_TYPE_TOPIC);
$dispatcher->addListener("#", new Amqp\Publisher\DefaultExchangePublisher($exchange, $broker), 1001);

$dispatcher->dispatch("kernel.error", (new Amqp\PublishEvent("error")));
$dispatcher->dispatch("kernel.warning", (new Amqp\PublishEvent("warning")));
$dispatcher->dispatch("kernel.notice", (new Amqp\PublishEvent("notice")));
$dispatcher->dispatch("kernel.waits", (new Amqp\PublishEvent("wait")));

// Publish local messages
// $dispatcher->dispatch("kernel.info", new SymfonyEvent());
// $dispatcher->dispatch("kernel.message", new SymfonyEvent());

$exchange = $broker->declareExchange(AMQP_DURABLE, "some-exchange", AMQP_EX_TYPE_TOPIC);
$dispatcher->addListener("#", new Amqp\Publisher\DefaultExchangePublisher($exchange, $broker), 1002);

// Publish messages without any listener
$dispatcher->dispatch("kernel.null", (new Amqp\PublishEvent("null")));
// Publish messages without direct listener
$dispatcher->dispatch("critical.kernel", (new Amqp\PublishEvent("null")));
// Publish wait message
$dispatcher->dispatch("kernel.waits", (new Amqp\PublishEvent("null")));
