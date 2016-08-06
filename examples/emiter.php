<?php
include __DIR__.'./../vendor/autoload.php';

use Kemer\Amqp;
use Kemer\Amqp\Addons as AmqpAddons;
use Symfony\Component\EventDispatcher\Event as SymfonyEvent;

$broker = new Amqp\Broker(
    "rabbit.docker", 5672, 'guest', 'guest'
);

$dispatcher = new Amqp\Dispatcher();
$dispatcher->addListener("#", new Amqp\Publisher\QueuePublisher($broker), 1000);
$dispatcher->addListener("#", new Amqp\Publisher\ExchangePublisher($broker), 1001);



$emiter = new App\Emiter($dispatcher);

// Publish queue-get command
$emiter->queueGetCommand("some-exchange", AMQP_DURABLE, "some-exchange-dlx");

// Publish message to exchange
$emiter->publishToExchange("some-exchange");

/**
 * Declare default exchnge
 */
$exchange = $broker->declareExchange(AMQP_DURABLE, "some-exchange", AMQP_EX_TYPE_TOPIC);
$dispatcher->addListener("#", new Amqp\Publisher\DefaultExchangePublisher($exchange, $broker), 1001);

/* Publish message to default exchange */
$emiter->publishToDefaultExchange();

/* Publish message to queue */
// $emiter->publishToQueue();

/* Publish local messages */
// $emiter->publishLocal();

/* Publish error messages */
// $emiter->publishErroring();
