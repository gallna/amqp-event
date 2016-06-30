# PHP module
https://github.com/pdezwart/php-amqp

## Documentation
http://php.net/manual/pl/book.amqp.php

## Examples
https://github.com/rabbitmq/rabbitmq-tutorials/tree/master/php-amqp

# Rabit implementation
https://github.com/php-amqplib/php-amqplib/tree/master/demo

# Dispatch

```

$envelope = new Amqp\Envelope();
$dispatcher = $amqp->getDispatcher();

// Publish message to queue
$dispatcher->dispatch("kernel.error", new Amqp\PublishEvent($envelope));
$dispatcher->dispatch("kernel.warning", new Amqp\PublishEvent($envelope));

// Publish message to exchange
$envelope->setExchangeName("exchangeName");

$dispatcher->dispatch("kernel.critical", new Amqp\PublishEvent($envelope));
$dispatcher->dispatch("kernel.notice", new Amqp\PublishEvent($envelope));
$dispatcher->dispatch("kernel.info", new Amqp\PublishEvent($envelope));
$dispatcher->dispatch("kernel.info", new Event());
$dispatcher->dispatch("kernel.message", new Event());

```
