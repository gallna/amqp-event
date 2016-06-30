<?php
include __DIR__.'/vendor/autoload.php';

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Kemer\Library\LibraryEvent;
use Kemer\Amqp;

$amqp = new Amqp\Amqp(
    new Amqp\Broker\PhpAmqp(
        "rabbitmq.amqp.development.companycheck.co.uk", 5672, 'guest', 'guest'
    )
);
$dispatcher = $amqp->getDispatcher();

// Send discover request
$dispatcher->dispatch(
    "LibraryEvent::DISCOVER",
    new Amqp\PublishEvent()
);

// Add event listeners
$dispatcher->addListener("kernel.*", [new App\Listener(), "onKernel"]);
$dispatcher->addListener('#', [new App\Listener(), "onAll"]);

// Add event subscriber
$dispatcher->addSubscriber(new App\Subscriber());

// Listen on queue
$queue = new AMQPQueue();
$queue->setName("queueName");
$amqp->listen($queue);

// Listen on exchange
$exchange = new AMQPExchange();
$exchange->setName("exchangeName");
$amqp->listen($exchange);

