<?php
include __DIR__.'./../vendor/autoload.php';

use Kemer\Amqp;
use Kemer\Amqp\Addons as AmqpAddons;
use Symfony\Component\EventDispatcher\GenericEvent;

$broker = new Amqp\Broker(
    getenv("AMQP_HOST") ?: "rabbit.docker",
    getenv("AMQP_PORT") ?: 5672,
    getenv("AMQP_LOGIN") ?: 'guest',
    getenv("AMQP_PASSWORD") ?: 'guest'
);

$dispatcher = new Amqp\Dispatcher();
$dispatcher->addSubscriber(new AmqpAddons\PostponeSubscriber($broker));
$dispatcher->addSubscriber(new AmqpAddons\DeadLetterSubscriber($broker));


// Add event listeners
$dispatcher->addListener('kernel.waits', [new App\Listener(), "onMessage"]);
$dispatcher->addListener("kernel.*", [new App\Listener(), "onKernel"]);
$dispatcher->addListener('#', [new App\Listener(), "onAll"]);


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
 * You can register listener to `kemer.error` event to handle exceptions.
 * Add Monolog to log everything
 */
$dispatcher->addSubscriber(new AmqpAddons\MonologSubscriber(
    $logger = new Monolog\Logger("AMQP")
));
$logger->pushHandler($handler = new Monolog\Handler\ErrorLogHandler());
$handler->setFormatter(new Monolog\Formatter\LineFormatter(
    $output = "\033[31m %level_name%\033[32m %message%\033[36m %context% \033[0m",
    $dateFormat = "g:i".
    false,
    false
));
$dispatcher->addSubscriber(new AmqpAddons\Command\QueueGetCommand($broker, $logger));
/**
 * postpone message on RuntimeException
 */
$dispatcher->addListener('kemer.error', function (GenericEvent $event, $eventName, $dispatcher) {
    if ($event["error"] instanceof \RuntimeException) {
        $dispatcher->dispatch(AmqpAddons\AddonsEvent::POSTPONE, $event->getSubject());
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
    $logger->info(
        sprintf("%s@%s NOT FOUND \n",
            $e->getEvent()->getExchangeName(),
            $e->getEvent()->getRoutingKey()
        )
    );
    $e->getEvent()->reject(false);
}
