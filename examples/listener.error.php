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
$dispatcher->addSubscriber(new Amqp\Command\QueueGetCommand($broker));

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
 * Error handling
 * You can register listener to `kemer.error` event to handle exceptions. See examples:
 */
$dispatcher->addListener('kemer.error', function (GenericEvent $event, $eventName, $dispatcher) {
    printf(
        "Error (%s)'%s' | %s@%s | %s \n",
        $event["error"]->getCode(),
        $event["error"]->getMessage(),
        $event->getSubject()->getExchangeName(),
        $event->getSubject()->getRoutingKey(),
        $event->getSubject()->isRedelivery() ? "redelivery" : "new"
    );
});

/**
 * postpone message on RuntimeException
 */
$dispatcher->addListener('kemer.error', function (GenericEvent $event, $eventName, $dispatcher) {
    if ($event["error"] instanceof \RuntimeException) {
        $dispatcher->dispatch(Amqp\AmqpEvent::POSTPONE, $event->getSubject());
    }
}, -1);

/**
 * reject message + requeue (if not requeued before) on error
 */
$dispatcher->addListener('kemer.error', function (GenericEvent $event, $eventName, $dispatcher) {
    $subject = $event->getSubject();
    if (!$subject->isConsumed()) {
        $subject->reject($subject->isRedelivery() ? false : AMQP_REQUEUE);
    }
}, -2);

/**
 * Catch ConsumerException here to get last consumed event
 */
try {
    $consumer->listen($queue, $exchange);
} catch (Amqp\Exceptions\ConsumerException $e) {
    $event = $e->getEvent();
    if (!$event->isConsumed()) {
        $event->reject($event->isRedelivery() ? false : AMQP_REQUEUE);
    }
} catch (Amqp\Exceptions\NotConsumedException $e) {
    printf("%s@%s NOT FOUND \n", $e->getEvent()->getExchangeName(), $e->getEvent()->getRoutingKey());
    $e->getEvent()->reject(false);
}
