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
 * @category   Magento
 * @package    \Magento\Event
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Event;

class Observer extends \Magento\Object
{
    /**
     * Checks the observer's event_regex against event's name
     *
     * @param \Magento\Event $event
     * @return boolean
     */
    public function isValidFor(\Magento\Event $event)
    {
        return $this->getEventName() === $event->getName();
    }

    /**
     * Dispatches an event to observer's callback
     *
     * @param \Magento\Event $event
     * @return \Magento\Event\Observer
     */
    public function dispatch(\Magento\Event $event)
    {
        if (!$this->isValidFor($event)) {
            return $this;
        }

        $callback = $this->getCallback();
        $this->setEvent($event);

        $_profilerKey = 'OBSERVER: '.(is_object($callback[0]) ? get_class($callback[0]) : (string)$callback[0]).' -> '.$callback[1];
        \Magento\Profiler::start($_profilerKey);
        call_user_func($callback, $this);
        \Magento\Profiler::stop($_profilerKey);

        return $this;
    }

    public function getName()
    {
        return $this->getData('name');
    }

    public function setName($data)
    {
        return $this->setData('name', $data);
    }

    public function getEventName()
    {
        return $this->getData('event_name');
    }

    public function setEventName($data)
    {
        return $this->setData('event_name', $data);
    }

    public function getCallback()
    {
        return $this->getData('callback');
    }

    public function setCallback($data)
    {
        return $this->setData('callback', $data);
    }

    /**
     * Get observer event object
     *
     * @return \Magento\Event
     */
    public function getEvent()
    {
        return $this->getData('event');
    }

    public function setEvent($data)
    {
        return $this->setData('event', $data);
    }
}
