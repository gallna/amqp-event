<?php
namespace Kemer\Amqp;

class PublishEvent extends AmqpEvent
{
    /** @var bool REMOVED from rabbitmq > 3-0 http://www.rabbitmq.com/blog/2012/11/19/breaking-things-with-rabbitmq-3-0 */
    private $immediate;

    /**
     * When publishing a message, the message must be routed to a valid queue. If it is not, an error will be returned.
     *  if the client publishes a message with the "mandatory" flag set to an exchange of "direct" type which is not bound to a queue.
     *
     * @var bool
     */
    private $mandatory;

    /**
     * Specifies the name of the exchange to publish to. The exchange name can be empty,
     * meaning the default exchange. If the exchange name is specified, and that exchange
     * does not exist, the server will raise a channel exception.
     *
     * @var string
     */
    private $exchangeName;

    /**
     * Specifies the routing key for the message.
     *
     * @var string
     */
    private $routingKey;

    /**
     * @param string $body
     * @param string $contentType
     */
    public function __construct($body = null, $contentType = null)
    {
        $this->setBody($body);
        $this->setContentType($contentType);
    }

    /**
     * Get the exchange name on which the message was published.
     *
     * @param the message was published.
     */
    public function setExchangeName($exchangeName)
    {
        $this->exchangeName = $exchangeName;
        return $this;
    }

    /**
     * Get the exchange name on which the message was published.
     *
     * @return string The exchange name on which the message was published.
     */
    public function getExchangeName()
    {
        return $this->exchangeName;
    }

    /**
     * Get the routing key of the message.
     *
     * @param The message routing key.
     */
    public function setRoutingKey($routingKey)
    {
        $this->routingKey = $routingKey;
        return $this;
    }

    /**
     * Get the routing key of the message.
     *
     * @return string The message routing key.
     */
    public function getRoutingKey()
    {
        return $this->routingKey;
    }

    /**
     * This flag tells the server how to react if a message cannot be routed to a queue.
     * Specifically, if mandatory is set and after running the bindings the message was placed on zero queues then the message is returned to the sender (with a basic.return). If mandatory had not been set under the same circumstances the server would silently drop the message.
     *
     * @return $this
     */
    public function setMandatory()
    {
        $this->mandatory = true;
        return $this;
    }

    /**
     * Indicates if message is mandatory
     *
     * @return boolean
     */
    public function isMandatory()
    {
        return $this->mandatory === true;
    }

    /**
     * Returns message flags
     *
     * @return integer
     */
    public function getFlags()
    {
        return $this->mandatory ? AMQP_MANDATORY : AMQP_NOPARAM;
    }
}
