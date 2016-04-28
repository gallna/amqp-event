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
    private $exchangeType = AMQP_EX_TYPE_TOPIC;

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
    public function connection($vhost = null)
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
            $vhost and $this->connection->setVhost($vhost);
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
    public function exchange($exchangeName, $exchangeType = null, $flags = null)
    {
        $exchange = new \AMQPExchange($this->channel());
        $exchangeType and $exchange->setName($exchangeName);
        $exchangeType and $exchange->setType($exchangeType);
        $flags and $exchange->setFlags($flags);
        $exchange->declareExchange();
        return $exchange;
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
    public function queue($queueName = null, $flags = AMQP_AUTODELETE)
    {
        if (!$this->queue) {
            $this->queue = new \AMQPQueue($this->channel());
            $this->queue->setFlags($flags);
            $queueName and $this->queue->setName($queueName);
            $this->queue->declareQueue();
        }
        return $this->queue;
    }

    /**
     * Publish a message to an exchange.
     *
     * Publish a message to the exchange represented by the AMQPExchange object.
     * AMQP_MANDATORY: When publishing a message, the message must be routed to a valid queue. If it is not, an error will be returned.
     * AMQP_IMMEDIATE When publishing a message, mark this message for immediate processing by the broker. (High priority message.)
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
    public function publish(Amqp\AmqpEvent $event)
    {
        $attributes = [
            "content_type" => $event->getContentType(),
        ];
        $this->exchange($event->getExchangeName(), $event->getType())
            ->publish(
                $event->getBody(),
                $event->getRoutingKey(),
                AMQP_NOPARAM,
                $attributes
            );
    }

    /**
     * Subscribe to channel
     *
     * @param string $channel
     */
    public function subscribe($exchangeName, $routingKey)
    {
        $this->queue()->bind(
            $exchangeName,
            $routingKey
        );
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
