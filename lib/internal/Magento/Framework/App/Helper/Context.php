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
 * @since 2.0.0
 */
class Context implements \Magento\Framework\ObjectManager\ContextInterface
{
    /**
     * @var \Magento\Framework\Module\Manager
     * @since 2.0.0
     */
    protected $_moduleManager;

    /**
     * @var  \Magento\Framework\Event\ManagerInterface
     * @since 2.0.0
     */
    protected $_eventManager;

    /**
     * @var \Psr\Log\LoggerInterface
     * @since 2.0.0
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\App\RequestInterface
     * @since 2.0.0
     */
    protected $_httpRequest;

    /**
     * @var \Magento\Framework\Cache\ConfigInterface
     * @since 2.0.0
     */
    protected $_cacheConfig;

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
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\App\RequestInterface $httpRequest
     * @param \Magento\Framework\Cache\ConfigInterface $cacheConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\HTTP\Header $httpHeader
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Module\Manager $moduleManager,
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
     * @return \Magento\Framework\Module\Manager
     * @since 2.0.0
     */
    public function getModuleManager()
    {
        return $this->_moduleManager;
    }

    /**
     * @return \Magento\Framework\UrlInterface
     * @since 2.0.0
     */
    public function getUrlBuilder()
    {
        return $this->_urlBuilder;
    }

    /**
     * @return \Magento\Framework\App\RequestInterface
     * @since 2.0.0
     */
    public function getRequest()
    {
        return $this->_httpRequest;
    }

    /**
     * @return \Magento\Framework\Cache\ConfigInterface
     * @since 2.0.0
     */
    public function getCacheConfig()
    {
        return $this->_cacheConfig;
    }

    /**
     * @return \Magento\Framework\Event\ManagerInterface
     * @since 2.0.0
     */
    public function getEventManager()
    {
        return $this->_eventManager;
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
     * @return \Magento\Framework\HTTP\Header
     * @since 2.0.0
     */
    public function getHttpHeader()
    {
        return $this->_httpHeader;
    }

    /**
     * @return \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     * @since 2.0.0
     */
    public function getRemoteAddress()
    {
        return $this->_remoteAddress;
    }

    /**
     * @return \Magento\Framework\Url\EncoderInterface
     * @since 2.0.0
     */
    public function getUrlEncoder()
    {
        return $this->urlEncoder;
    }

    /**
     * @return \Magento\Framework\Url\DecoderInterface
     * @since 2.0.0
     */
    public function getUrlDecoder()
    {
        return $this->urlDecoder;
    }

    /**
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    public function getScopeConfig()
    {
        return $this->scopeConfig;
    }
}
