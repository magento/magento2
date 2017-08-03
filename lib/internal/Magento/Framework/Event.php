<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Event object and dispatcher
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework;

/**
 * @api
 * @since 2.0.0
 */
class Event extends \Magento\Framework\DataObject
{
    /**
     * Observers collection
     *
     * @var \Magento\Framework\Event\Observer\Collection
     * @since 2.0.0
     */
    protected $_observers;

    /**
     * Constructor
     *
     * Initializes observers collection
     *
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(array $data = [])
    {
        $this->_observers = new \Magento\Framework\Event\Observer\Collection();
        parent::__construct($data);
    }

    /**
     * Returns all the registered observers for the event
     *
     * @return \Magento\Framework\Event\Observer\Collection
     * @since 2.0.0
     */
    public function getObservers()
    {
        return $this->_observers;
    }

    /**
     * Register an observer for the event
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @since 2.0.0
     */
    public function addObserver(\Magento\Framework\Event\Observer $observer)
    {
        $this->getObservers()->addObserver($observer);
        return $this;
    }

    /**
     * Removes an observer by its name
     *
     * @param string $observerName
     * @return $this
     * @since 2.0.0
     */
    public function removeObserverByName($observerName)
    {
        $this->getObservers()->removeObserverByName($observerName);
        return $this;
    }

    /**
     * Dispatches the event to registered observers
     *
     * @return $this
     * @since 2.0.0
     */
    public function dispatch()
    {
        $this->getObservers()->dispatch($this);
        return $this;
    }

    /**
     * Retrieve event name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName()
    {
        return isset($this->_data['name']) ? $this->_data['name'] : null;
    }

    /**
     * @param string $data
     * @return $this
     * @since 2.0.0
     */
    public function setName($data)
    {
        $this->_data['name'] = $data;
        return $this;
    }

    /**
     * @return mixed
     * @since 2.0.0
     */
    public function getBlock()
    {
        return $this->_getData('block');
    }
}
