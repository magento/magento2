<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
 */
class Event extends \Magento\Framework\DataObject
{
    /**
     * Observers collection
     *
     * @var \Magento\Framework\Event\Observer\Collection
     */
    protected $_observers;

    /**
     * Constructor
     *
     * Initializes observers collection
     *
     * @param array $data
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
     */
    public function getName()
    {
        return isset($this->_data['name']) ? $this->_data['name'] : null;
    }

    /**
     * @param string $data
     * @return $this
     */
    public function setName($data)
    {
        $this->_data['name'] = $data;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBlock()
    {
        return $this->_getData('block');
    }
}
