<?php
namespace Kemer\Amqp\Consumer;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Kemer\Amqp\Exchange\ExchangeName;

abstract class AbstractConsumer extends ExchangeName
{
    /**
     * @var string
     */
    protected $queueName;

    /**
     * @var bool
     */
    protected $noack;

    /**
     * @param AMQPExchange $exchange
     * @param string $exchangeName
     */
    public function __construct($exchangeName, $queueName = null, $noack = false)
    {
        $this->setExchangeName($exchangeName);
        $this->queueName = $queueName;
        $this->noack = $noack;
    }

    /**
     * Returns Consumer queue name
     *
     * @return string
     */
    public function getQueueName()
    {
        return $this->queueName;
    }

    /**
     * Returns Consumer queue name
     *
     * @return string
     */
    public function getFlags()
    {
        return $this->noack ? AMQP_NOACK : AMQP_NOPARAM;
    }
}
