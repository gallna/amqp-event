<?php
include __DIR__.'./../vendor/autoload.php';

use Kemer\Amqp;

$broker = new Amqp\Broker("rabbit.docker", 5672, 'guest', 'guest');

$dispatcher = new Amqp\Dispatcher();
$dispatcher->addSubscriber(new Amqp\Facade\PostponeSubscriber($broker));
$dispatcher->addSubscriber(new Amqp\Facade\DeadLetterSubscriber($broker));
$dispatcher->addListener("#", new Amqp\Publisher\QueuePublisher($broker), 1000);
$dispatcher->addListener("#", new Amqp\Publisher\ExchangePublisher($broker), 1001);

// Add event listeners
$dispatcher->addListener('kernel.message', [new App\Listener(), "onMessage"]);
$dispatcher->addListener("kernel.*", [new App\Listener(), "onKernel"]);
$dispatcher->addListener('kernel.#', [new App\Listener(), "onAll"]);

// Add event subscriber
$dispatcher->addSubscriber(new App\Subscriber());

// Send discover request
$dispatcher->dispatch("some-kernel", new Amqp\PublishEvent());


$consumer = new Amqp\Consumer($dispatcher);

// Listen on named queue
$consumer->listen($broker->declareQueue(AMQP_DURABLE, "error-queue"));

// Listen on existing shared queue (load balanced)
$consumer->listen($broker->declareQueue(AMQP_PASSIVE, "existing-queue"));
