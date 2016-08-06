<?php
namespace Kemer\Amqp;

use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * The routing key used for a topic exchange MUST consist of zero or more words delimited by dots.
 * Each word may contain the letters A-Z and a-z and digits 0-9.
 * https://www.rabbitmq.com/resources/specs/amqp0-9-1.pdf
 */
class Dispatcher extends EventDispatcher
{
    /**
     * {@inheritdoc}
     */
    public function getListeners($eventName = null)
    {
        if ($eventName === null) {
            return parent::getListeners($eventName);
        }
        $listeners = [];
        foreach (parent::getListeners() as $name => $eventListeners) {
            $namePattern = str_replace(["*", "#"], ["(\w+)", "([\w\.]+)"], $name);
            if (preg_match("~^$namePattern$~", $eventName)) {
                $listeners = array_merge($listeners, $eventListeners);
            }
        }
        return $listeners;
    }
}
