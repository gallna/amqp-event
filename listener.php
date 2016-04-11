<?php
include __DIR__.'/vendor/autoload.php';

use Kemer\Amqp;
use Kemer\Amqp\Broker\RabbitMQ;
use Kemer\Amqp\Broker\PhpAmqp;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\OnlineFeedListener;

// create event dispatcher
$eventDispatcher = new EventDispatcher();

// create Amqp server
$amqp = new Amqp\Amqp(
    new PhpAmqp('localhost', 5672, 'guest', 'guest'),
    $eventDispatcher
);

// Add event listeners
$eventDispatcher->addListener("kernel.*", [new App\Listener(), "onKernel"]);
$eventDispatcher->addListener('#', [new App\Listener(), "onAll"]);

// Add event subscriber
$eventDispatcher->addSubscriber(new App\Subscriber());

$amqp->run();
