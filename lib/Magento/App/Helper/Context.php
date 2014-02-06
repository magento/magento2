<?php
/**
 * Abstract helper context
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
namespace Magento\App\Helper;

class Context implements \Magento\ObjectManager\ContextInterface
{
    /**
     * @var \Magento\TranslateInterface
     */
    protected $_inlineFactory;

    /**
     * @var \Magento\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var  \Magento\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\App\RequestInterface
     */
    protected $_httpRequest;

    /**
     * @var \Magento\Cache\ConfigInterface
     */
    protected $_cacheConfig;

    /**
     * @var \Magento\Core\Model\App
     */
    protected $_app;

    /**
     * @var \Magento\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\HTTP\Header
     */
    protected $_httpHeader;

    /**
     * @var \Magento\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $_remoteAddress;

    /**
     * @param \Magento\Logger $logger
     * @param \Magento\Translate\InlineFactory $inlineFactory
     * @param \Magento\Module\Manager $moduleManager
     * @param \Magento\App\RequestInterface $httpRequest
     * @param \Magento\Cache\ConfigInterface $cacheConfig
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Model\App $app
     * @param \Magento\UrlInterface $urlBuilder
     * @param \Magento\HTTP\Header $httpHeader
     * @param \Magento\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Logger $logger,
        \Magento\Translate\InlineFactory $inlineFactory,
        \Magento\Module\Manager $moduleManager,
        \Magento\App\RequestInterface $httpRequest,
        \Magento\Cache\ConfigInterface $cacheConfig,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Model\App $app,
        \Magento\UrlInterface $urlBuilder,
        \Magento\HTTP\Header $httpHeader,
        \Magento\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
    ) {
        $this->_inlineFactory = $inlineFactory;
        $this->_moduleManager = $moduleManager;
        $this->_httpRequest = $httpRequest;
        $this->_cacheConfig = $cacheConfig;
        $this->_eventManager = $eventManager;
        $this->_logger = $logger;
        $this->_app = $app;
        $this->_urlBuilder = $urlBuilder;
        $this->_httpHeader = $httpHeader;
        $this->_remoteAddress = $remoteAddress;
    }

    /**
     * @return \Magento\Translate\InlineFactory
     */
    public function getInlineFactory()
    {
        return $this->_inlineFactory;
    }

    /**
     * @return \Magento\Module\Manager
     */
    public function getModuleManager()
    {
        return $this->_moduleManager;
    }

    /**
     * @return \Magento\Core\Model\App
     */
    public function getApp()
    {
        return $this->_app;
    }

    /**
     * @return \Magento\UrlInterface
     */
    public function getUrlBuilder()
    {
        return $this->_urlBuilder;
    }

    /**
     * @return \Magento\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->_httpRequest;
    }

    /**
     * @return \Magento\Cache\ConfigInterface
     */
    public function getCacheConfig()
    {
        return $this->_cacheConfig;
    }

    /**
     * @return \Magento\Event\ManagerInterface
     */
    public function getEventManager()
    {
        return $this->_eventManager;
    }

    /**
     * @return \Magento\Logger
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * @return \Magento\HTTP\Header
     */
    public function getHttpHeader()
    {
        return $this->_httpHeader;
    }

    /**
     * @return \Magento\HTTP\PhpEnvironment\RemoteAddress
     */
    public function getRemoteAddress()
    {
        return $this->_remoteAddress;
    }
}
