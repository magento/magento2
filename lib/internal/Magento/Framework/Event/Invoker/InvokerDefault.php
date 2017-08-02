<?php
/**
 * Default event invoker
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Event\Invoker;

use Magento\Framework\Event\Observer;

/**
 * Class \Magento\Framework\Event\Invoker\InvokerDefault
 *
 * @since 2.0.0
 */
class InvokerDefault implements \Magento\Framework\Event\InvokerInterface
{
    /**
     * Observer model factory
     *
     * @var \Magento\Framework\Event\ObserverFactory
     * @since 2.0.0
     */
    protected $_observerFactory;

    /**
     * Application state
     *
     * @var \Magento\Framework\App\State
     * @since 2.0.0
     */
    protected $_appState;

    /**
     * @param \Magento\Framework\Event\ObserverFactory $observerFactory
     * @param \Magento\Framework\App\State $appState
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Event\ObserverFactory $observerFactory,
        \Magento\Framework\App\State $appState
    ) {
        $this->_observerFactory = $observerFactory;
        $this->_appState = $appState;
    }

    /**
     * Dispatch event
     *
     * @param array $configuration
     * @param Observer $observer
     * @return void
     * @since 2.0.0
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
        $this->_callObserverMethod($object, $observer);
    }

    /**
     * @param \Magento\Framework\Event\ObserverInterface $object
     * @param Observer $observer
     * @return $this
     * @throws \LogicException
     * @since 2.0.0
     */
    protected function _callObserverMethod($object, $observer)
    {
        if ($object instanceof \Magento\Framework\Event\ObserverInterface) {
            $object->execute($observer);
        } elseif ($this->_appState->getMode() == \Magento\Framework\App\State::MODE_DEVELOPER) {
            throw new \LogicException(
                sprintf(
                    'Observer "%s" must implement interface "%s"',
                    get_class($object),
                    \Magento\Framework\Event\ObserverInterface::class
                )
            );
        }
        return $this;
    }
}
