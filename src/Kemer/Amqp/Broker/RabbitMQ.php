<?php
namespace Kemer\Amqp\Broker;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Kemer\Amqp\AmqpEvent;

class RabbitMQ
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
     * @var callable
     */
    private $callback;

    /**
     * @var string
     */
    private $queueName;


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
            $this->connection = new AMQPStreamConnection(
                'server.rabbitmq.development.weeb.online',
                5672,
                'guest',
                'guest'
            );
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
            $this->channel = $this->connection()->channel();
            $this->channel
                ->exchange_declare('topic_logs', 'topic', false, false, false);
            $this->queueName = $this->channel
                ->queue_declare("", false, false, true, false)[0];
        }
        return $this->channel;
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
        $this->channel()->basic_publish(
            new AMQPMessage($message),
            'topic_logs',
            $channel
        );
    }

    /**
     * Subscribe to channel
     *
     * @param string $channel
     */
    public function subscribe($topic)
    {
        $this->channel()
            ->queue_bind($this->queueName, 'topic_logs', $topic);
    }


    /**
     * Run the Amqp event
     *
     * @return void
     */
    public function run($callback)
    {
        $this->callback = $callback;
        $channel = $this->channel();
        $channel->basic_consume(
            $this->queueName,
            '',
            false,
            true,
            false,
            false,
            [$this, "onMessage"]
        );

        while(count($channel->callbacks)) {
            $channel->wait();
        }
    }

    /**
     * Create and dispatch SSDP message
     *
     * @param string $message
     * @return void
     */
    public function onMessage($message)
    {
        $event = new AmqpEvent(
            $message->delivery_info['routing_key'],
            $message->body
        );
        call_user_func($this->callback, $event);
    }

    /**
     * Close connection and channel
     *
     * @return $this
     */
    public function __destruct()
    {
        if ($this->channel) {
            $this->channel->close();
        }
        if ($this->connection) {
            $this->connection->close();
        }
    }
}
