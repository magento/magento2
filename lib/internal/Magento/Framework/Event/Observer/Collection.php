<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Event observer collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Event\Observer;

/**
 * @api
 * @since 2.0.0
 */
class Collection
{
    /**
     * Array of observers
     *
     * @var array
     * @since 2.0.0
     */
    protected $_observers;

    /**
     * Initializes observers
     * @since 2.0.0
     */
    public function __construct()
    {
        $this->_observers = [];
    }

    /**
     * Returns all observers in the collection
     *
     * @return array
     * @since 2.0.0
     */
    public function getAllObservers()
    {
        return $this->_observers;
    }

    /**
     * Returns observer by its name
     *
     * @param string $observerName
     * @return \Magento\Framework\Event\Observer
     * @since 2.0.0
     */
    public function getObserverByName($observerName)
    {
        return $this->_observers[$observerName];
    }

    /**
     * Adds an observer to the collection
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @since 2.0.0
     */
    public function addObserver(\Magento\Framework\Event\Observer $observer)
    {
        $this->_observers[$observer->getName()] = $observer;
        return $this;
    }

    /**
     * Removes an observer from the collection by its name
     *
     * @param string $observerName
     * @return $this
     * @since 2.0.0
     */
    public function removeObserverByName($observerName)
    {
        unset($this->_observers[$observerName]);
        return $this;
    }

    /**
     * Dispatches an event to all observers in the collection
     *
     * @param \Magento\Framework\Event $event
     * @return $this
     * @since 2.0.0
     */
    public function dispatch(\Magento\Framework\Event $event)
    {
        foreach ($this->_observers as $observer) {
            $observer->dispatch($event);
        }
        return $this;
    }
}
