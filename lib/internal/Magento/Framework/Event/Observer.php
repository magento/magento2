<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Event;

use Magento\Framework\Event;

/**
 * @api
 */
class Observer extends \Magento\Framework\DataObject
{
    /**
     * Checks the observer's event_regex against event's name
     *
     * @param Event $event
     * @return boolean
     */
    public function isValidFor(Event $event)
    {
        return $this->getEventName() === $event->getName();
    }

    /**
     * Dispatches an event to observer's callback
     *
     * @param Event $event
     * @return $this
     */
    public function dispatch(Event $event)
    {
        if (!$this->isValidFor($event)) {
            return $this;
        }

        $callback = $this->getCallback();
        $this->setEvent($event);

        $_profilerKey = 'OBSERVER: ';
        if (is_object($callback[0])) {
            $_profilerKey .= get_class($callback[0]);
        } else {
            $_profilerKey .= (string)$callback[0];
        }
        $_profilerKey .= ' -> ' . $callback[1];

        \Magento\Framework\Profiler::start($_profilerKey);
        call_user_func($callback, $this);
        \Magento\Framework\Profiler::stop($_profilerKey);

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getData('name');
    }

    /**
     * @param string $data
     * @return \Magento\Framework\DataObject
     */
    public function setName($data)
    {
        return $this->setData('name', $data);
    }

    /**
     * @return string
     */
    public function getEventName()
    {
        return $this->getData('event_name');
    }

    /**
     * @param string $data
     * @return \Magento\Framework\DataObject
     */
    public function setEventName($data)
    {
        return $this->setData('event_name', $data);
    }

    /**
     * @return string
     */
    public function getCallback()
    {
        return $this->getData('callback');
    }

    /**
     * @param string $data
     * @return \Magento\Framework\DataObject
     */
    public function setCallback($data)
    {
        return $this->setData('callback', $data);
    }

    /**
     * Get observer event object
     *
     * @return Event
     */
    public function getEvent()
    {
        return $this->getData('event');
    }

    /**
     * @param mixed $data
     * @return \Magento\Framework\DataObject
     */
    public function setEvent($data)
    {
        return $this->setData('event', $data);
    }
}
