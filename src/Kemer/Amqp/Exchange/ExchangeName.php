<?php
namespace Kemer\Amqp\Exchange;

class ExchangeName
{
    /**
     * @var string
     */
    protected $exchangeName;

    /**
     * @param string $exchangeName
     */
    public function __construct($exchangeName)
    {
        $this->exchangeName = $exchangeName;
    }

    /**
     * Exchange name setter
     *
     * @param string $exchangeName
     */
    public function setExchangeName($exchangeName)
    {
        $this->exchangeName = $exchangeName;
    }

    /**
     * Exchange name getter
     *
     * @return string
     */
    public function getExchangeName()
    {
        return $this->exchangeName;
    }
}
