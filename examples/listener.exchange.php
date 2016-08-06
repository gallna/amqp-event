<?php
include __DIR__.'./../vendor/autoload.php';

use Kemer\Amqp;
use Symfony\Component\EventDispatcher\GenericEvent;

$broker = new Amqp\Broker(
    "rabbit.docker", 5672, 'guest', 'guest'
);

$dispatcher = new Amqp\Dispatcher();
$dispatcher->addSubscriber(new Amqp\Facade\PostponeSubscriber($broker));
$dispatcher->addSubscriber(new Amqp\Facade\DeadLetterSubscriber($broker));

// Add event listeners
$dispatcher->addListener('kernel.message', [new App\Listener(), "onMessage"]);
// $dispatcher->addListener("kernel.*", [new App\Listener(), "onKernel"]);
// $dispatcher->addListener('#', [new App\Listener(), "onAll"]);


// Add event subscriber
$dispatcher->addSubscriber(new App\Subscriber());

// dispatch local messages
// $dispatcher->dispatch("some.kernel", new Amqp\PublishEvent());



$consumer = new Amqp\Consumer($dispatcher);


/**
 * Listen on shared queue (load balanced)
 *
 * @throws AMQPQueueException if queue does not exists
 */
//$queue = $broker->declareQueue(AMQP_PASSIVE, "some-queue");
$queue = $broker->queue(AMQP_DURABLE, "some-queue");
$queue->setArgument("x-dead-letter-exchange", "some-exchange-dlx");
$queue->declareQueue();
// $consumer->listen($queue);

// Listen on temporary queue - and bind it to exchange
//$queue = $broker->declareQueue(AMQP_AUTODELETE);

/**
 * Ensure exchange already exists
 *
 * @throws AMQPExchangeException if exchange does not exists
 */
//$exchange = $broker->declareExchange(AMQP_PASSIVE, "some-exchange", AMQP_EX_TYPE_TOPIC);

/**
 * Create exchange if does not exists
 *
 * @throws AMQPExchangeException if exchange does not exists
 */
$exchange = $broker->declareExchange(AMQP_DURABLE, "some-exchange", AMQP_EX_TYPE_TOPIC);

/**
 * Listen on shared queue and bind queue to exchange
 *
 * @throws AMQPQueueException if queue does not exists
 */
//$consumer->listen($queue, $exchange);

/**
 * Catch ConsumerException here to get last AmqpEvent
 */
try {
    $consumer->listen($queue, $exchange);
} catch (Amqp\Exceptions\ConsumerException $e) {
    $event = $e->getEvent();
    if (!$event->isConsumed()) {
        $event->reject($event->isRedelivery() ? false : AMQP_REQUEUE);
    }
}
