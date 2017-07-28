<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Helper;

/**
 * Abstract helper
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @since 2.0.0
 */
abstract class AbstractHelper
{
    /**
     * Helper module name
     *
     * @var string
     * @since 2.0.0
     */
    protected $_moduleName;

    /**
     * Request object
     *
     * @var \Magento\Framework\App\RequestInterface
     * @since 2.0.0
     */
    protected $_request;

    /**
     * @var \Magento\Framework\Module\Manager
     * @since 2.0.0
     */
    protected $_moduleManager;

    /**
     * @var \Psr\Log\LoggerInterface
     * @since 2.0.0
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\UrlInterface
     * @since 2.0.0
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Framework\HTTP\Header
     * @since 2.0.0
     */
    protected $_httpHeader;

    /**
     * Event manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     * @since 2.0.0
     */
    protected $_eventManager;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     * @since 2.0.0
     */
    protected $_remoteAddress;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     * @since 2.0.0
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     * @since 2.0.0
     */
    protected $urlDecoder;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Cache\ConfigInterface
     * @since 2.1.0
     */
    protected $_cacheConfig;

    /**
     * @param Context $context
     * @since 2.0.0
     */
    public function __construct(Context $context)
    {
        $this->_moduleManager = $context->getModuleManager();
        $this->_logger = $context->getLogger();
        $this->_request = $context->getRequest();
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_httpHeader = $context->getHttpHeader();
        $this->_eventManager = $context->getEventManager();
        $this->_remoteAddress = $context->getRemoteAddress();
        $this->_cacheConfig = $context->getCacheConfig();
        $this->urlEncoder = $context->getUrlEncoder();
        $this->urlDecoder = $context->getUrlDecoder();
        $this->scopeConfig = $context->getScopeConfig();
    }

    /**
     * Retrieve request object
     *
     * @return \Magento\Framework\App\RequestInterface
     * @since 2.0.0
     */
    protected function _getRequest()
    {
        return $this->_request;
    }

    /**
     * Retrieve helper module name
     *
     * @return string
     * @since 2.0.0
     */
    protected function _getModuleName()
    {
        if (!$this->_moduleName) {
            $class = get_class($this);
            $this->_moduleName = substr($class, 0, strpos($class, '\\Helper'));
        }
        return str_replace('\\', '_', $this->_moduleName);
    }

    /**
     * Check whether or not the module output is enabled in Configuration
     *
     * @param string $moduleName Full module name
     * @return boolean
     * use \Magento\Framework\Module\Manager::isOutputEnabled()
     * @since 2.0.0
     */
    public function isModuleOutputEnabled($moduleName = null)
    {
        if ($moduleName === null) {
            $moduleName = $this->_getModuleName();
        }
        return $this->_moduleManager->isOutputEnabled($moduleName);
    }

    /**
     * Retrieve url
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     * @since 2.0.0
     */
    protected function _getUrl($route, $params = [])
    {
        return $this->_urlBuilder->getUrl($route, $params);
    }
}
