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


/**
 * Event observer collection
 * 
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Event\Observer;

class Collection
{
    /**
     * Array of observers
     *
     * @var array
     */
    protected $_observers;

    /**
     * Initializes observers
     */
    public function __construct()
    {
        $this->_observers = array();
    }

    /**
     * Returns all observers in the collection
     *
     * @return array
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
     */
    public function dispatch(\Magento\Framework\Event $event)
    {
        foreach ($this->_observers as $observer) {
            $observer->dispatch($event);
        }
        return $this;
    }
}
