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

To ensure message delivery to queue/exchange - dispatch `MandatoryEvent` instead of `Event`
# Exception handling

[PHP SPL exceptions](http://php.net/manual/en/spl.exceptions.php#spl.exceptions.tree)

## Logic Exceptions

Logic Exceptions are for errors that occur at compile time. Since PHP has no compile
time in the sense this is meant, it usually is interpreted as "errors occuring during
development", (like when the developer forgot to pass a depedency or rooting key)

LogicException are moved to `dead-letter` exchange because they represents error in
the program logic. This kind of exception should lead directly to a fix in code
and requeue messages

**DomainException** (a LogicException subset) is thew when naither of listeners consumed
the event (either by acknowledging or rejecting message).
This can't be fixed otherwise than removing not supported route or adding missing acknowledgement


## Runtime Exceptions

Runtime Exceptions are for unforseen errors (usually stemming from User Input) when the code is run.

Those exceptions may be triggered by temporary connection issues, rate limits etc.
They are moved automatically to `wait` exchange to retry them later.

## Throwable

It's up to developer how to handle any other `Throwable` by wrapping Consumer into try-catch block.

 - it might be moved to `headers` exchange - and retried after implementing fix
 - might be moved to `dead-letter` exchange by message broker
 - rejecting erroring messages with `AMQP_REQUEUE` flag creates stream of one and the same messages.
    Keep an eye on retry-count to avoid issues

