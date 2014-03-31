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
namespace Magento\Model;

class Context implements \Magento\ObjectManager\ContextInterface
{
    /**
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventDispatcher;

    /**
     * @var \Magento\App\CacheInterface
     */
    protected $_cacheManager;

    /**
     * @var \Magento\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\Model\ActionValidator\RemoveAction
     */
    protected $_actionValidator;

    /**
     * @param \Magento\Logger $logger
     * @param \Magento\Event\ManagerInterface $eventDispatcher
     * @param \Magento\App\CacheInterface $cacheManager
     * @param \Magento\App\State $appState
     * @param ActionValidator\RemoveAction $actionValidator
     */
    public function __construct(
        \Magento\Logger $logger,
        \Magento\Event\ManagerInterface $eventDispatcher,
        \Magento\App\CacheInterface $cacheManager,
        \Magento\App\State $appState,
        \Magento\Model\ActionValidator\RemoveAction $actionValidator
    ) {
        $this->_eventDispatcher = $eventDispatcher;
        $this->_cacheManager = $cacheManager;
        $this->_appState = $appState;
        $this->_logger = $logger;
        $this->_actionValidator = $actionValidator;
    }

    /**
     * @return \Magento\App\CacheInterface
     */
    public function getCacheManager()
    {
        return $this->_cacheManager;
    }

    /**
     * @return \Magento\Event\ManagerInterface
     */
    public function getEventDispatcher()
    {
        return $this->_eventDispatcher;
    }

    /**
     * @return \Magento\Logger
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * @return \Magento\App\State
     */
    public function getAppState()
    {
        return $this->_appState;
    }

    /**
     * @return \Magento\Model\ActionValidator\RemoveAction
     */
    public function getActionValidator()
    {
        return $this->_actionValidator;
    }
}
