<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Event;

use Magento\Framework\Event\ObserverInterface;

/**
 * Observer of Magento events triggered using \Magento\TestFramework\EventManager::dispatch()
 */
class Magento implements ObserverInterface
{
    /**
     * Used when Magento framework instantiates the class on its own and passes nothing to the constructor
     *
     * @var \Magento\TestFramework\EventManager
     */
    protected static $_defaultEventManager;

    /**
     * @var \Magento\TestFramework\EventManager
     */
    protected $_eventManager;

    /**
     * @var $_eventObject
     */
    protected static $_eventObject;

    /**
     * @var $testPrepared
     */
    protected static $testPrepared = false;

    /**
     * set TestPrepared value
     */
    public static function setTestPrepared($value)
    {
        self::$testPrepared = $value;
    }

    /**
     * get TestPrepared value
     */
    public static function getTestPrepared()
    {
        return  self::$testPrepared;
    }

    /**
     * Assign current Event Object
     *
     * @param $eventObject
     */
    public static function setCurrentEventObject($eventObject = null)
    {
        self::$_eventObject = $eventObject;
    }

    /**
     * Get Current Event Object
     */
    public static function getCurrentEventObject()
    {
        return self::$_eventObject;
    }

    /**
     * Assign default event manager instance
     *
     * @param \Magento\TestFramework\EventManager $eventManager
     */
    public static function setDefaultEventManager(?\Magento\TestFramework\EventManager $eventManager = null)
    {
        self::$_defaultEventManager = $eventManager;
    }

    /**
     * Get Default Event Manager
     */
    public static function getDefaultEventManager()
    {
        return self::$_defaultEventManager;
    }

    /**
     * Constructor
     *
     * @param \Magento\TestFramework\EventManager $eventManager
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct($eventManager = null)
    {
        $this->_eventManager = $eventManager ?: self::$_defaultEventManager;
        if (!$this->_eventManager instanceof \Magento\TestFramework\EventManager) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Instance of the "Magento\TestFramework\EventManager" is expected.')
            );
        }
    }

    /**
     * Handler for 'core_app_init_current_store_after' event, that converts it into 'initStoreAfter'
     * @param \Magento\Framework\Event\Observer $observer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_eventManager->fireEvent('initStoreAfter');
    }
}
