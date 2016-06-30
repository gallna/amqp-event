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
    public function channel()
    {
        return new \AMQPChannel($this->connection());
        if (!$this->channel) {
            $this->channel = new \AMQPChannel($this->connection());
        }
        return $this->channel;
    }

    /**
     * Returns exchange
     * Exchange types: direct, topic, headers and fanout.
     * AMQP_DURABLE AMQP_PASSIVE
     *
     * @return object
     */
    public function exchange($name = null, $flags = null)
    {
        $exchange = new \AMQPExchange($this->channel());
        $name and $exchange->setName($name);
        $flags and $exchange->setFlags($flags);
        return $exchange;
    }


    /**
     * Returns queue
     * AMQP_AUTODELETE queue is deleted when last consumer unsubscribes
     * AMQP_DURABLE the queue will survive a broker restart
     * AMQP_EXCLUSIVE used by only one connection and the queue will be deleted
     *  when that connection closes
     *
     * @param integer $flags A bitmask of flags:
     *                       AMQP_DURABLE, AMQP_PASSIVE,
     *                       AMQP_EXCLUSIVE, AMQP_AUTODELETE.
     * @return object
     */
    public function queue($name = null, $flags = null)
    {
        $queue = new \AMQPQueue($this->channel());
        $name and $queue->setName($name);
        $flags and $queue->setFlags($flags);
        return $queue;
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
