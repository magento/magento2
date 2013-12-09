<?php
/**
 * Core Session Context Model
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
namespace Magento\Core\Model\Session;

class Context implements \Magento\ObjectManager\ContextInterface
{
    /**
     * @var \Magento\Core\Model\Session\Validator
     */
    protected $_validator;

    /**
     * @var \Magento\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_storeConfig;

    /**
     * @var string
     */
    protected $_saveMethod;

    /**
     * Core message
     *
     * @var \Magento\Message\Factory
     */
    protected $messageFactory;

    /**
     * Core message collection factory
     *
     * @var \Magento\Message\CollectionFactory
     */
    protected $messagesFactory;

    /**
     * @var \Magento\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Core\Model\Session\Validator $validator
     * @param \Magento\Logger $logger
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Message\CollectionFactory $messagesFactory
     * @param \Magento\Message\Factory $messageFactory
     * @param \Magento\App\RequestInterface $request
     * @param \Magento\App\State $appState
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param string $saveMethod
     */
    public function __construct(
        \Magento\Core\Model\Session\Validator $validator,
        \Magento\Logger $logger,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Message\CollectionFactory $messagesFactory,
        \Magento\Message\Factory $messageFactory,
        \Magento\App\RequestInterface $request,
        \Magento\App\State $appState,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        $saveMethod = 'files'
    ) {
        $this->_validator = $validator;
        $this->_logger = $logger;
        $this->_eventManager = $eventManager;
        $this->_storeConfig = $coreStoreConfig;
        $this->_saveMethod = $saveMethod;
        $this->messagesFactory = $messagesFactory;
        $this->messageFactory = $messageFactory;
        $this->_request = $request;
        $this->_appState = $appState;
        $this->_storeManager = $storeManager;
    }

    /**
     * @return \Magento\Event\ManagerInterface
     */
    public function getEventManager()
    {
        return $this->_eventManager;
    }

    /**
     * @return \\Magento\Logger
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * @return \Magento\Core\Model\Store\Config
     */
    public function getStoreConfig()
    {
        return $this->_storeConfig;
    }

    /**
     * @return \Magento\Core\Model\Session\Validator
     */
    public function getValidator()
    {
        return $this->_validator;
    }

    /**
     * @return string
     */
    public function getSaveMethod()
    {
        return $this->_saveMethod;
    }

    /**
     * @return \Magento\App\State
     */
    public function getAppState()
    {
        return $this->_appState;
    }

    /**
     * @return \Magento\Message\Factory
     */
    public function getMessageFactory()
    {
        return $this->messageFactory;
    }

    /**
     * @return \Magento\Message\CollectionFactory
     */
    public function getMessagesFactory()
    {
        return $this->messagesFactory;
    }

    /**
     * @return \Magento\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * @return \Magento\Core\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->_storeManager;
    }
}
