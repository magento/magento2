<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Event;

use Magento\Framework\Event;

class Observer extends \Magento\Framework\Object
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
     * @return \Magento\Framework\Object
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
     * @return \Magento\Framework\Object
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
     * @return \Magento\Framework\Object
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
     * @return \Magento\Framework\Object
     */
    public function setEvent($data)
    {
        return $this->setData('event', $data);
    }
}
