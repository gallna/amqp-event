<?php
namespace Kemer\Amqp\Broker;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Kemer\Amqp;

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
    private $queueName = null;


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
     * AMQP_DURABLE AMQP_PASSIVE
     *
     * @return object
     */
    public function exchange($flags = null)
    {
        if (!$this->exchange) {
            $this->exchange = new \AMQPExchange($this->channel());
            $this->exchange->setType($this->exchangeType);
            $this->exchange->setName($this->exchangeName);
            $flags and $this->exchange->setFlags($flags);
            $this->exchange->declareExchange();
        }
        return $this->exchange;
    }


    /**
     * Returns channel
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
    public function queue($flags = AMQP_AUTODELETE)
    {
        if (!$this->queue) {
            $this->queue = new \AMQPQueue($this->channel());
            $this->queue->setFlags($flags);
            $this->queueName and $this->queue->setName($this->queueName);
            $this->queue->declareQueue();
        }
        return $this->queue;
    }

    /**
     * Publish a message to an exchange.
     *
     * Publish a message to the exchange represented by the AMQPExchange object.
     *
     * @param string  $message     The message to publish.
     * @param string  $routing_key The optional routing key to which to publish to.
     * @param integer $flags       One or more of AMQP_MANDATORY and AMQP_IMMEDIATE.
     * @param array   $attributes  One of content_type, content_encoding,
     *                             message_id, user_id, app_id, delivery_mode,
     *                             priority, timestamp, expiration, type
     *                             or reply_to, headers.
     *
     * @throws AMQPExchangeException   On failure.
     * @throws AMQPChannelException    If the channel is not open.
     * @throws AMQPConnectionException If the connection to the broker was lost.
     *
     * @return boolean TRUE on success or FALSE on failure.
     *
     */
    public function publish($routingKey, Amqp\AmqpEvent $event)
    {
        $attributes = [
            "content_type" => $event->getContentType(),
        ];
        $this->exchange()->publish(
            $event->getBody(),
            $routingKey,
            AMQP_MANDATORY,
            $attributes
        );
    }

    /**
     * Subscribe to channel
     *
     * @param string $channel
     */
    public function subscribe($routingKey)
    {
        $this->queue()->bind($this->exchange()->getName(), $routingKey);
    }


    /**
     * Run the Amqp event
     *
     * @return void
     */
    public function run($callback)
    {
        $this->queue()->consume($callback);
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
