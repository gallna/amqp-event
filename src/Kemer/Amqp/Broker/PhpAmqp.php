<?php
namespace Kemer\Amqp\Broker;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Kemer\Amqp\AmqpEvent;

class PhpAmqp
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
     * @var AMQPStreamConnection
     */
    private $connection;

    /**
     * @var object
     */
    private $channel;

    /**
     * @var object
     */
    private $exchange;
    private $exchangeName = "exchange_name";
    private $exchangeType = AMQP_EX_TYPE_TOPIC;
    /**
     * @var callable
     */
    private $callback;

    /**
     * @var AMQPQueue
     */
    private $queue;


    /**
     * @param string $host
     * @param integer $port
     * @param string $user
     * @param string $password
     */
    public function __construct($host, $port, $user, $password)
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Returns Rabbit connection
     *
     * @return AMQPStreamConnection
     */
    public function connection()
    {
        if (!$this->connection) {
            if (!$this->host || !$this->port) {
                throw \InvalidArgumentException(
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
        if (!$this->channel) {
            $this->channel = new \AMQPChannel($this->connection());
        }
        return $this->channel;
    }

    /**
     * Returns exchange
     * Exchange types: direct, topic, headers and fanout.
     *
     * @return object
     */
    public function exchange()
    {
        if (!$this->exchange) {
            $this->exchange = new \AMQPExchange($this->channel());
            $this->exchange->setType($this->exchangeType);
            $this->exchange->setName($this->exchangeName);
            $this->exchange->declare();
        }
        return $this->exchange;
    }


    /**
     * Returns channel
     *
     * @return object
     */
    public function queue()
    {
        if (!$this->queue) {
            $this->queue = new \AMQPQueue($this->channel());
            $this->queue->setFlags(AMQP_EXCLUSIVE);
            $this->queue->declare();
        }
        return $this->queue;
    }

    /**
     * Publish data to channel
     *
     * @param string $channel
     * @param array $message
     * @return array
     */
    public function publish($channel, $message)
    {
        $this->exchange()->publish($message, $channel);
    }

    /**
     * Subscribe to channel
     *
     * @param string $channel
     */
    public function subscribe($topic)
    {
        $this->queue()->bind($this->exchangeName, $topic);
    }


    /**
     * Run the Amqp event
     *
     * @return void
     */
    public function run($callback)
    {
        $this->callback = $callback;
        $this->queue->consume([$this, "onMessage"]);
    }

    /**
     * Create and dispatch SSDP message
     *
     * @param string $message
     * @return void
     */
    public function onMessage($message)
    {
        $event = new AmqpEvent($message->getBody(), $message);
        $event->setName($message->getRoutingKey());
        call_user_func($this->callback, $event);
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
