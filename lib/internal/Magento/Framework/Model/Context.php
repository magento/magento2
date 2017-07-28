<?php
/**
 * Abstract model context
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model;

/**
 * Constructor modification point for Magento\Framework\Model\AbstractModel.
 *
 * All context classes were introduced to allow for backwards compatible constructor modifications
 * of classes that were supposed to be extended by extension developers.
 *
 * Do not call methods of this class directly.
 *
 * As Magento moves from inheritance-based APIs all such classes will be deprecated together with
 * the classes they were introduced for.
 *
 * @api
 * @since 2.0.0
 */
class Context implements \Magento\Framework\ObjectManager\ContextInterface
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     * @since 2.0.0
     */
    protected $_eventDispatcher;

    /**
     * @var \Magento\Framework\App\CacheInterface
     * @since 2.0.0
     */
    protected $_cacheManager;

    /**
     * @var \Psr\Log\LoggerInterface
     * @since 2.0.0
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\App\State
     * @since 2.0.0
     */
    protected $_appState;

    /**
     * @var \Magento\Framework\Model\ActionValidator\RemoveAction
     * @since 2.0.0
     */
    protected $_actionValidator;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Event\ManagerInterface $eventDispatcher
     * @param \Magento\Framework\App\CacheInterface $cacheManager
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\Model\ActionValidator\RemoveAction $actionValidator
     * @since 2.0.0
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Event\ManagerInterface $eventDispatcher,
        \Magento\Framework\App\CacheInterface $cacheManager,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Model\ActionValidator\RemoveAction $actionValidator
    ) {
        $this->_eventDispatcher = $eventDispatcher;
        $this->_cacheManager = $cacheManager;
        $this->_appState = $appState;
        $this->_logger = $logger;
        $this->_actionValidator = $actionValidator;
    }

    /**
     * @return \Magento\Framework\App\CacheInterface
     * @since 2.0.0
     */
    public function getCacheManager()
    {
        return $this->_cacheManager;
    }

    /**
     * @return \Magento\Framework\Event\ManagerInterface
     * @since 2.0.0
     */
    public function getEventDispatcher()
    {
        return $this->_eventDispatcher;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     * @since 2.0.0
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * @return \Magento\Framework\App\State
     * @since 2.0.0
     */
    public function getAppState()
    {
        return $this->_appState;
    }

    /**
     * @return \Magento\Framework\Model\ActionValidator\RemoveAction
     * @since 2.0.0
     */
    public function getActionValidator()
    {
        return $this->_actionValidator;
    }
}
