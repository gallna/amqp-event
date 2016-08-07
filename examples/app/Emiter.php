<?php
namespace App;

use Kemer\Amqp;
use Kemer\Amqp\Addons as AmqpAddons;

class Emiter
{
    private $dispatcher;
    /**
     * {@inheritDoc}
     */
    public function __construct($dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    private function display(AmqpEvent $event, $eventName, $methodName, $color = "1;32")
    {
        echo sprintf(
            "\033[%sm %s@%s\033[0m\033[36m [%s]\033[0m %s: %s \n",
            $color,
            $event->getExchangeName(),
            $event->getRoutingKey(),
            $eventName,
            $methodName,
            $event->getBody()
        );
    }

    public function queueGetCommand($exchangeName, $queueFlags, $queueName)
    {
        // Publish queue-get command
        $headers = ["x-command-queue-get" => ["flags" => $queueFlags, "name" => $queueName]];
        $this->dispatcher->dispatch(
            AmqpAddons\AddonsEvent::QUEUE_GET_COMMAND,
            (new Amqp\PublishEvent("queue-get"))
                ->setExchangeName($exchangeName)->setHeaders($headers)
        );
    }

    public function messagePostpone($exchangeName)
    {
        $this->dispatcher->dispatch(
            "kernel.wait",
            (new Amqp\PublishEvent("wait"))->setExchangeName($exchangeName)
        );
    }

    public function publishToQueue()
    {
        // Publish message to queue
        $dispatcher->dispatch("some-exchange", new Amqp\PublishEvent());
        $dispatcher->dispatch("some-queue", new Amqp\PublishEvent());
    }

    public function publishToExchange($exchangeName = "some-exchange")
    {
        // Publish message to exchange
        $this->dispatcher->dispatch(
            "kernel.error",
            (new Amqp\PublishEvent("error"))->setExchangeName($exchangeName)
        );
        $this->dispatcher->dispatch(
            "kernel.warning",
            (new Amqp\PublishEvent("warning"))->setExchangeName($exchangeName)
        );
    }

    public function publishToDefaultExchange()
    {
        $this->dispatcher->dispatch("kernel.info", (new Amqp\PublishEvent("info")));
        $this->dispatcher->dispatch("kernel.notice", (new Amqp\PublishEvent("notice")));
    }

    public function publishErroring()
    {
        // Publish messages without any listener
        $this->dispatcher->dispatch("kernel.null", (new Amqp\PublishEvent("null")));
        // Publish messages without direct listener
        $this->dispatcher->dispatch("critical.kernel", (new Amqp\PublishEvent("null")));
        // Publish wait message
        $this->dispatcher->dispatch("kernel.waits", (new Amqp\PublishEvent("null")));
    }

    public function publishLocal()
    {
        // Publish local messages
        $this->dispatcher->dispatch("kernel.info", new SymfonyEvent());
        $this->dispatcher->dispatch("kernel.message", new SymfonyEvent());
    }
}
