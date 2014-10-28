<?php
/**
 * Abstract model context
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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Model;

class Context implements \Magento\Framework\ObjectManager\ContextInterface
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventDispatcher;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cacheManager;

    /**
     * @var \Magento\Framework\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\Framework\Model\ActionValidator\RemoveAction
     */
    protected $_actionValidator;

    /**
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Event\ManagerInterface $eventDispatcher
     * @param \Magento\Framework\App\CacheInterface $cacheManager
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\Model\ActionValidator\RemoveAction $actionValidator
     */
    public function __construct(
        \Magento\Framework\Logger $logger,
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
     */
    public function getCacheManager()
    {
        return $this->_cacheManager;
    }

    /**
     * @return \Magento\Framework\Event\ManagerInterface
     */
    public function getEventDispatcher()
    {
        return $this->_eventDispatcher;
    }

    /**
     * @return \Magento\Framework\Logger
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * @return \Magento\Framework\App\State
     */
    public function getAppState()
    {
        return $this->_appState;
    }

    /**
     * @return \Magento\Framework\Model\ActionValidator\RemoveAction
     */
    public function getActionValidator()
    {
        return $this->_actionValidator;
    }
}
