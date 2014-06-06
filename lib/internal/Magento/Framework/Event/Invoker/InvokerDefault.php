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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Event\Invoker;

use Zend\Stdlib\Exception\LogicException;
use Magento\Framework\Event\Observer;

class InvokerDefault implements \Magento\Framework\Event\InvokerInterface
{
    /**
     * Observer model factory
     *
     * @var \Magento\Framework\Event\ObserverFactory
     */
    protected $_observerFactory;

    /**
     * Application state
     *
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @param \Magento\Framework\Event\ObserverFactory $observerFactory
     * @param \Magento\Framework\App\State $appState
     */
    public function __construct(\Magento\Framework\Event\ObserverFactory $observerFactory, \Magento\Framework\App\State $appState)
    {
        $this->_observerFactory = $observerFactory;
        $this->_appState = $appState;
    }

    /**
     * Dispatch event
     *
     * @param array $configuration
     * @param Observer $observer
     * @return void
     */
    public function dispatch(array $configuration, Observer $observer)
    {
        /** Check whether event observer is disabled */
        if (isset($configuration['disabled']) && true === $configuration['disabled']) {
            return;
        }

        if (isset($configuration['shared']) && false === $configuration['shared']) {
            $object = $this->_observerFactory->create($configuration['instance']);
        } else {
            $object = $this->_observerFactory->get($configuration['instance']);
        }
        $this->_callObserverMethod($object, $configuration['method'], $observer);
    }

    /**
     * Performs non-existent observer method calls protection
     *
     * @param object $object
     * @param string $method
     * @param Observer $observer
     * @return $this
     * @throws \LogicException
     */
    protected function _callObserverMethod($object, $method, $observer)
    {
        if (method_exists($object, $method) && is_callable([$object, $method])) {
            $object->{$method}($observer);
        } elseif ($this->_appState->getMode() == \Magento\Framework\App\State::MODE_DEVELOPER) {
            throw new \LogicException('Method "' . $method . '" is not defined in "' . get_class($object) . '"');
        }
        return $this;
    }
}
