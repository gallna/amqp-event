<?php
include __DIR__.'./../vendor/autoload.php';

use Kemer\Amqp;

$broker = new Amqp\Broker(
    "localhost", 5672, 'guest', 'guest'
);

$dispatcher = new Amqp\Dispatcher();
$dispatcher->addSubscriber(new Amqp\Publisher\DefaultPublisher($broker));
$dispatcher->addSubscriber(new Amqp\Publisher\RetryPublisher($broker));



// Add event listeners
$dispatcher->addListener('kernel.message', [new App\Listener(), "onMessage"]);
$dispatcher->addListener("kernel.*", [new App\Listener(), "onKernel"]);
$dispatcher->addListener('#', [new App\Listener(), "onAll"]);

// Add event subscriber
$dispatcher->addSubscriber(new App\Subscriber());



// Send discover request
$dispatcher->dispatch("some.kernel", new Amqp\PublishEvent());



// Listen on queue
$queue = new \AMQPQueue($broker->channel());
$queue->setName("queueName");
$consumer = new Amqp\Consumer($dispatcher);
$consumer->listen($queue);

// Listen on exchange
$exchange = new \AMQPExchange($broker->channel());
$exchange->setName("exchangeName");
$consumer = new Amqp\ExchangeConsumer($consumer, $broker);
$consumer->listen($exchange);

