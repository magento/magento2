<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Event\Invoker;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\State;

/**
 * Default Invoker.
 */
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
     * @var State
     */
    protected $_appState;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\Framework\Event\ObserverFactory $observerFactory
     * @param State $appState
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\Event\ObserverFactory $observerFactory,
        State $appState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig = null,
        LoggerInterface $logger = null
    ) {
        $this->_observerFactory = $observerFactory;
        $this->_appState = $appState;
        $this->scopeConfig = $scopeConfig ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(ScopeConfigInterface::class);
        $this->logger = $logger ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(LoggerInterface::class);
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

        if (isset($configuration['ifconfig']) && $this->scopeConfig->isSetFlag($configuration['ifconfig']) === false) {
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
     * Execute Observer.
     *
     * @param \Magento\Framework\Event\ObserverInterface $object
     * @param Observer $observer
     * @return $this
     * @throws \LogicException
     */
    protected function _callObserverMethod($object, $observer)
    {
        if ($object instanceof \Magento\Framework\Event\ObserverInterface) {
            $object->execute($observer);
        } elseif ($this->_appState->getMode() == State::MODE_DEVELOPER) {
            throw new \LogicException(
                sprintf(
                    'Observer "%s" must implement interface "%s"',
                    get_class($object),
                    \Magento\Framework\Event\ObserverInterface::class
                )
            );
        } else {
            $this->logger->warning(sprintf(
                'Observer "%s" must implement interface "%s"',
                get_class($object),
                \Magento\Framework\Event\ObserverInterface::class
            ));
        }
        return $this;
    }
}
