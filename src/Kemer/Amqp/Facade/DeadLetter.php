<?php
namespace Kemer\Amqp\Facade;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Kemer\Amqp\Broker;
use Kemer\Amqp;

class DeadLetter
{
    /**
     * @var Broker
     */
    protected $broker;

    /**
     * @param DispatcherInterface $dispatcher
     * @param AbstractConsumer $consumer
     * @param Broker $broker
     */
    public function __construct(Amqp\Broker $broker)
    {
        $this->broker = $broker;
    }

    /**
     * On dispatch event listener - called on any event
     *
     * @param Event $event
     * @param string $eventName
     * @return void
     */
    public function deadLetter(AmqpEvent $event, \Exception $exception)
    {
        $queue = $this->getQueue($event->getExchangeName());
        $exchange = $this->getExchange($event->getExchangeName());
        $queue->bind($exchange->getName(), $event->getRoutingKey());
        $exchange->publish(
            $event->getBody(),
            $event->getRoutingKey(),
            AMQP_MANDATORY,
            $event->attributes()
        );
    }

    /**
     * Creates wait queue
     *
     * @return AMQPQueue
     */
    private function getQueue($name)
    {
        return $this->broker->declareQueue(AMQP_DURABLE, $name.".dlx");
    }

    /**
     * Creates wait exchange
     *
     * @return AMQPExchange
     */
    private function getExchange($name)
    {
        return $this->broker->declareExchange(AMQP_DURABLE, $name.".dlx", AMQP_EX_TYPE_DIRECT);
    }
}
