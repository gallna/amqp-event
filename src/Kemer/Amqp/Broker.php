<?php
namespace Kemer\Amqp;

class Broker
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var integer
     */
    private $port;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $vhost;

    /**
     * @var AMQPConnection
     */
    private $connection;

    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * @param string $host
     * @param integer $port
     * @param string $user
     * @param string $password
     */
    public function __construct($host, $port, $user, $password, $vhost = null)
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->vhost = $vhost;
    }

    /**
     * Returns AMQP connection
     *
     * @return AMQPConnection
     */
    public function connection()
    {
        if (!$this->connection) {
            if (!$this->host || !$this->port) {
                throw new \InvalidArgumentException(
                    sprintf("Remote host or port undefined '%s:%s' ",
                        $this->host,
                        $this->port
                    )
                );
            }
            $this->connection = new \AMQPConnection();
            $this->connection->setHost($this->host);
            $this->connection->setPort($this->port);
            $this->connection->setLogin($this->user);
            $this->connection->setPassword($this->password);
            $this->vhost and $this->connection->setVhost($this->vhost);
            $this->connection->connect();
        }
        return $this->connection;
    }


    /**
     * Returns channel
     *
     * @return object
     */
    public function channel($reuse = false)
    {
        if (!$this->channel || !$reuse) {
            $this->channel = new \AMQPChannel($this->connection());
        }
        return $this->channel;
    }

    /**
     * Returns exchange
     * Exchange types: direct, topic, headers and fanout.
     * AMQP_EX_TYPE_DIRECT AMQP_EX_TYPE_FANOUT AMQP_EX_TYPE_TOPIC AMQP_EX_TYPE_HEADER
     * AMQP_DURABLE Durable exchanges will survive a broker restart,
     * AMQP_PASSIVE Passive exchanges will not be redeclared, but the broker will throw
     *              an error if the exchange does not exist.
     *
     * @return AMQPExchange
     * @throws AMQPChannelException
     * @throws AMQPExchangeException
     */
    public function exchange($flags = null, $name = null, $type = null)
    {
        $exchange = new \AMQPExchange($this->channel());
        $flags and $exchange->setFlags($flags);
        $type and $exchange->setType($type);
        $name and $exchange->setName($name);
        return $exchange;
    }

    /**
     * Returns declared exchange
     *
     * @return AMQPExchange
     * @throws AMQPChannelException
     * @throws AMQPExchangeException
     */
    public function declareExchange(...$parameters)
    {
        $exchange = $this->exchange(...$parameters);
        $exchange->declareExchange();
        return $exchange;
    }

    /**
     * Checks if the named exchange exists.
     * In response RabbitMQ responds with a channel exception if the exchange does not exist.
     * AMQP_PASSIVE Passive exchanges will not be redeclared, but the broker will throw
     *              an error if the exchange does not exist.
     *
     * @return bool
     */
    public function exchangeExists($name, $type)
    {
        try {
            return $this->declareExchange(AMQP_PASSIVE, $name, $type) instanceof \AMQPExchange;
        } catch (\AMQPExchangeException $e) {
            if (strpos($e->getMessage(), "404") > 0) {
                return false;
            }
            throw $e;
        }
    }

    /**
     * Returns queue
     * AMQP_AUTODELETE queue is deleted when last consumer unsubscribes
     * AMQP_DURABLE the queue will survive a broker restart
     * AMQP_EXCLUSIVE used by only one connection and the queue will be deleted
     *  when that connection closes
     *
     * AMQP_DURABLE Durable queues will survive a broker restart,
     * AMQP_PASSIVE Passive will not be redeclared, but the broker will throw
     *              an error if the queue does not exist.
     * @param integer $flags A bitmask of flags:
     *                       AMQP_DURABLE, AMQP_PASSIVE,
     *                       AMQP_EXCLUSIVE, AMQP_AUTODELETE.
     * @return AMQPQueue
     * @throws AMQPQueueException
     */
    public function queue($flags = null, $name = null)
    {
        $queue = new \AMQPQueue($this->channel());
        $name and $queue->setName($name);
        $flags and $queue->setFlags($flags);
        return $queue;
    }

    /**
     * Returns declared exchange
     *
     * @return AMQPQueue
     * @throws AMQPChannelException
     */
    public function declareQueue(...$parameters)
    {
        $queue = $this->queue(...$parameters);
        $queue->declareQueue();
        return $queue;
    }

    /**
     * Checks if the named queue exists.
     * In response RabbitMQ responds with a channel exception if the queue does not exist.
     * AMQP_PASSIVE Passive exchanges will not be redeclared, but the broker will throw
     *              an error if the exchange does not exist.
     *
     * @return bool
     */
    public function queueExists($name)
    {
        try {
            return $this->declareQueue(AMQP_PASSIVE, $name) instanceof \AMQPQueue;
        } catch (\AMQPChannelException $e) {
            if (strpos($e->getMessage(), "404") > 0) {
                echo "OK";
                return false;
            }
            throw $e;
        }
    }

    /**
     * Close connection and channel
     *
     * @return $this
     */
    public function __destruct()
    {
        if ($this->connection) {
            $this->connection->disconnect();
        }
    }
}
