<?php
namespace Kemer\Amqp;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

class Amqp
{
    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var consumer
     */
    protected $consumer;

    /**
     * @var broker
     */
    protected $broker;

    /**
     * Constructor
     *
     * @param Dispatcher $dispatcher
     */
    public function __construct($broker, Dispatcher $dispatcher = null)
    {
        $this->broker = $broker;
        $this->dispatcher = $dispatcher ?: new Dispatcher();
    }

    /**
     * Returns broker
     *
     * @return
     */
    public function getBroker()
    {
        return $this->broker;
    }

    /**
     * Get event dispatcher
     *
     * @return dispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * Returns broker
     *
     * @return
     */
    public function addPublisher(Publisher\AbstractPublisher $publisher, $flags = null)
    {
        $exchange = $this->getExchange($publisher, $flags);
        $this->getDispatcher()
            ->addSubscriber(new Exchange\Subscriber($exchange));
    }

    /**
     * Run the Amqp listener
     *
     * @return void
     */
    public function listen(Consumer\AbstractConsumer $consumer, $flags = null)
    {
        $events = array_keys($this->getDispatcher()->getListeners());
        $exchange = $this->getExchange($consumer, $flags);
        $queue = $this->getBroker()->queue($consumer->getQueueName());
        foreach ($events as $eventName) {
            $queue->bind($consumer->getExchangeName(), $eventName);
        }
        $queue->consume(
            is_callable($consumer) ? $consumer : new Consumer($this->getDispatcher()),
            $consumer->getFlags()
        );
    }

    /**
     * Creates exchange object
     *
     * @param ExchangeName $exchangeName
     * @param string $flags
     * @return AMQPExchange
     */
    private function getExchange(Exchange\ExchangeName $exchangeName, $flags)
    {
        return $this->getBroker()->exchange(
            $exchangeName->getExchangeName(),
            $this->getExchangeType($exchangeName),
            $flags
        );
    }

    /**
     * Check event interface to find exchange type
     *
     * @param Event $event
     * @return string
     */
    private function getExchangeType($object)
    {
        switch (true) {
            case $object instanceof Exchange\Direct:
                return AMQP_EX_TYPE_DIRECT;
            case $object instanceof Exchange\Fanout:
                return AMQP_EX_TYPE_FANOUT;
            case $object instanceof Exchange\Headers:
                return AMQP_EX_TYPE_HEADERS;
            case $object instanceof Exchange\Topic:
                return AMQP_EX_TYPE_TOPIC;
        }
        throw new \InvalidArgumentException(
            "Not recognized exchange type"
        );
    }
}
