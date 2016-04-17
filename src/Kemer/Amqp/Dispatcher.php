<?php
namespace Kemer\Amqp;

use Symfony\Component\EventDispatcher\EventDispatcher;

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
