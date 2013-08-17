<?php
/**
 * Default event invoker
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Model_Event_Invoker_InvokerDefault implements Mage_Core_Model_Event_InvokerInterface
{
    /**
     * Observer model factory
     *
     * @var Mage_Core_Model_ObserverFactory
     */
    protected $_observerFactory;

    /**
     * Application state
     *
     * @var Mage_Core_Model_App_State
     */
    protected $_appState;

    /**
     * @param Mage_Core_Model_ObserverFactory $observerFactory
     * @param Mage_Core_Model_App_State $appState
     */
    public function __construct(Mage_Core_Model_ObserverFactory $observerFactory, Mage_Core_Model_App_State $appState)
    {
        $this->_observerFactory = $observerFactory;
        $this->_appState = $appState;
    }

    /**
     * Dispatch event
     *
     * @param array $configuration
     * @param Varien_Event_Observer $observer
     */
    public function dispatch(array $configuration, Varien_Event_Observer $observer)
    {
        switch ($configuration['type']) {
            case 'disabled':
                break;
            case 'object':
            case 'model':
                $object = $this->_observerFactory->create($configuration['model']);
                $this->_callObserverMethod($object, $configuration['method'], $observer);
                break;
            default:
                $object = $this->_observerFactory->get($configuration['model']);
                $this->_callObserverMethod($object, $configuration['method'], $observer);
                break;
        }
    }

    /**
     * Performs non-existent observer method calls protection
     *
     * @param object $object
     * @param string $method
     * @param Varien_Event_Observer $observer
     * @return Mage_Core_Model_Event_InvokerInterface
     * @throws Mage_Core_Exception
     */
    protected function _callObserverMethod($object, $method, $observer)
    {
        if (method_exists($object, $method)) {
            $object->$method($observer);
        } elseif ($this->_appState->getMode() == Mage_Core_Model_App_State::MODE_DEVELOPER) {
            Mage::throwException('Method "' . $method . '" is not defined in "' . get_class($object) . '"');
        }
        return $this;
    }
}
