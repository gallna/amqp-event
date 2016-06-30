<?php
namespace Kemer\Amqp\Exceptions;

class DelayException extends \Exception
{
    private $expiration;
    private $retryCount;

    public function __construct($expiration, $retryCount = 0)
    {
        $this->expiration = $expiration;
        $this->retryCount = $retryCount;
        parent::__construct("Message delayed: {$expiration} ms, {$retryCount} times");
    }

    public function getExpiration($expiration = 10000, $retryCount = null)
    {
        return $this->expiration;
    }

    public function getRetryCount()
    {
        return $this->retryCount;
    }
}
