<?php
/**
 * Default event invoker
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Event\Invoker;

use Magento\Framework\Event\Observer;
use Zend\Stdlib\Exception\LogicException;

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
