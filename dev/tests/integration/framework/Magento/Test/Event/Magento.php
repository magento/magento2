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
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Observer of Magento events triggered using Mage::dispatchEvent()
 */
class Magento_Test_Event_Magento
{
    /**
     * Used when Magento framework instantiates the class on its own and passes nothing to the constructor
     *
     * @var Magento_Test_EventManager
     */
    protected static $_defaultEventManager;

    /**
     * @var Magento_Test_EventManager
     */
    protected $_eventManager;

    /**
     * Assign default event manager instance
     *
     * @param Magento_Test_EventManager $eventManager
     */
    public static function setDefaultEventManager(Magento_Test_EventManager $eventManager = null)
    {
        self::$_defaultEventManager = $eventManager;
    }

    /**
     * Constructor
     *
     * @param Magento_Test_EventManager $eventManager
     * @throws Magento_Exception
     */
    public function __construct($eventManager = null)
    {
        $this->_eventManager = $eventManager ?: self::$_defaultEventManager;
        if (!($this->_eventManager instanceof Magento_Test_EventManager)) {
            throw new Magento_Exception('Instance of the "Magento_Test_EventManager" is expected.');
        }
    }

    /**
     * Handler for 'controller_front_init_before' event, that converts it into 'initFrontControllerBefore'
     */
    public function initFrontControllerBefore()
    {
        $this->_eventManager->fireEvent('initFrontControllerBefore');
    }
}
