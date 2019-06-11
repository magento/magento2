<?php
/**
 * Abstract helper context
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Helper;

/**
 * Constructor modification point for Magento\Framework\App\Helper.
 *
 * All context classes were introduced to allow for backwards compatible constructor modifications
 * of classes that were supposed to be extended by extension developers.
 *
 * Do not call methods of this class directly.
 *
 * As Magento moves from inheritance-based APIs all such classes will be deprecated together with
 * the classes they were introduced for.
 */
class Context implements \Magento\Framework\ObjectManager\ContextInterface
{
    /**
     * @var \Magento\Framework\Module\ModuleManagerInterface
     */
    protected $_moduleManager;

    /**
     * @var  \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_httpRequest;

    /**
     * @var \Magento\Framework\Cache\ConfigInterface
     */
    protected $_cacheConfig;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Framework\HTTP\Header
     */
    protected $_httpHeader;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $_remoteAddress;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Module\ModuleManagerInterface $moduleManager
     * @param \Magento\Framework\App\RequestInterface $httpRequest
     * @param \Magento\Framework\Cache\ConfigInterface $cacheConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\HTTP\Header $httpHeader
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Module\ModuleManagerInterface $moduleManager,
        \Magento\Framework\App\RequestInterface $httpRequest,
        \Magento\Framework\Cache\ConfigInterface $cacheConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\HTTP\Header $httpHeader,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_moduleManager = $moduleManager;
        $this->_httpRequest = $httpRequest;
        $this->_cacheConfig = $cacheConfig;
        $this->_eventManager = $eventManager;
        $this->_logger = $logger;
        $this->_urlBuilder = $urlBuilder;
        $this->_httpHeader = $httpHeader;
        $this->_remoteAddress = $remoteAddress;
        $this->urlEncoder = $urlEncoder;
        $this->urlDecoder = $urlDecoder;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get module manager.
     *
     * @return \Magento\Framework\Module\ModuleManagerInterface
     */
    public function getModuleManager()
    {
        return $this->_moduleManager;
    }

    /**
     * Get url builder.
     *
     * @return \Magento\Framework\UrlInterface
     */
    public function getUrlBuilder()
    {
        return $this->_urlBuilder;
    }

    /**
     * Get request.
     *
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->_httpRequest;
    }

    /**
     * Get cache configs.
     *
     * @return \Magento\Framework\Cache\ConfigInterface
     */
    public function getCacheConfig()
    {
        return $this->_cacheConfig;
    }

    /**
     * Get event manager.
     *
     * @return \Magento\Framework\Event\ManagerInterface
     */
    public function getEventManager()
    {
        return $this->_eventManager;
    }

    /**
     * Get logger.
     *
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * Get http header.
     *
     * @return \Magento\Framework\HTTP\Header
     */
    public function getHttpHeader()
    {
        return $this->_httpHeader;
    }

    /**
     * Get remote address.
     *
     * @return \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    public function getRemoteAddress()
    {
        return $this->_remoteAddress;
    }

    /**
     * Get url encoder.
     *
     * @return \Magento\Framework\Url\EncoderInterface
     */
    public function getUrlEncoder()
    {
        return $this->urlEncoder;
    }

    /**
     * Get url decoder.
     *
     * @return \Magento\Framework\Url\DecoderInterface
     */
    public function getUrlDecoder()
    {
        return $this->urlDecoder;
    }

    /**
     * Get scope config.
     *
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public function getScopeConfig()
    {
        return $this->scopeConfig;
    }
}
