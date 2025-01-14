<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element;

use Magento\Framework\Cache\LockGuardedCacheLoader;
use Magento\Framework\App\ObjectManager;

/**
 * Constructor modification point for Magento\Framework\View\Element\AbstractBlock.
 *
 * All context classes were introduced to allow for backwards compatible constructor modifications
 * of classes that were supposed to be extended by extension developers.
 *
 * Do not call methods of this class directly.
 *
 * As Magento moves from inheritance-based APIs all such classes will be deprecated together with
 * the classes they were introduced for.
 *
 * @SuppressWarnings(PHPMD)
 *
 * @api
 * @since 100.0.2
 */
class Context implements \Magento\Framework\ObjectManager\ContextInterface
{
    /**
     * Request
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * Layout
     *
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * Event manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * URL builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * Cache
     *
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cache;

    /**
     * Design
     *
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $_design;

    /**
     * Session
     *
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_session;

    /**
     * SID Resolver
     *
     * @var \Magento\Framework\Session\SidResolverInterface
     */
    protected $_sidResolver;

    /**
     * Scope config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * View config model
     *
     * @var \Magento\Framework\View\ConfigInterface
     */
    protected $_viewConfig;

    /**
     * Cache state
     *
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    protected $_cacheState;

    /**
     * Logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * Filter manager
     *
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $_filterManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var LockGuardedCacheLoader
     */
    private $lockQuery;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\View\ConfigInterface $viewConfig
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param LockGuardedCacheLoader $lockQuery
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\View\ConfigInterface $viewConfig,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        ?LockGuardedCacheLoader $lockQuery = null
    ) {
        $this->_request = $request;
        $this->_layout = $layout;
        $this->_eventManager = $eventManager;
        $this->_urlBuilder = $urlBuilder;
        $this->_cache = $cache;
        $this->_design = $design;
        $this->_session = $session;
        $this->_sidResolver = $sidResolver;
        $this->_scopeConfig = $scopeConfig;
        $this->_assetRepo = $assetRepo;
        $this->_viewConfig = $viewConfig;
        $this->_cacheState = $cacheState;
        $this->_logger = $logger;
        $this->_escaper = $escaper;
        $this->_filterManager = $filterManager;
        $this->_localeDate = $localeDate;
        $this->inlineTranslation = $inlineTranslation;
        $this->lockQuery = $lockQuery ?: ObjectManager::getInstance()->get(LockGuardedCacheLoader::class);
    }

    /**
     * Get cache
     *
     * @return \Magento\Framework\App\CacheInterface
     */
    public function getCache()
    {
        return $this->_cache;
    }

    /**
     * Get design package
     *
     * @return \Magento\Framework\View\DesignInterface
     */
    public function getDesignPackage()
    {
        return $this->_design;
    }

    /**
     * Get event manager
     *
     * @return \Magento\Framework\Event\ManagerInterface
     */
    public function getEventManager()
    {
        return $this->_eventManager;
    }

    /**
     * Get layout
     *
     * @return \Magento\Framework\View\LayoutInterface
     */
    public function getLayout()
    {
        return $this->_layout;
    }

    /**
     * Get request
     *
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Get session
     *
     * @return \Magento\Framework\Session\SessionManagerInterface
     */
    public function getSession()
    {
        return $this->_session;
    }

    /**
     * Get SID resolver
     *
     * @return \Magento\Framework\Session\SidResolverInterface
     */
    public function getSidResolver()
    {
        return $this->_sidResolver;
    }

    /**
     * Get scope config
     *
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public function getScopeConfig()
    {
        return $this->_scopeConfig;
    }

    /**
     * Get inline translation status object
     *
     * @return \Magento\Framework\Translate\Inline\StateInterface
     */
    public function getInlineTranslation()
    {
        return $this->inlineTranslation;
    }

    /**
     * Get URL builder
     *
     * @return \Magento\Framework\UrlInterface
     */
    public function getUrlBuilder()
    {
        return $this->_urlBuilder;
    }

    /**
     * Get asset service
     *
     * @return \Magento\Framework\View\Asset\Repository
     */
    public function getAssetRepository()
    {
        return $this->_assetRepo;
    }

    /**
     * Get view config
     *
     * @return \Magento\Framework\View\ConfigInterface
     */
    public function getViewConfig()
    {
        return $this->_viewConfig;
    }

    /**
     * Get cache state
     *
     * @return \Magento\Framework\App\Cache\StateInterface
     */
    public function getCacheState()
    {
        return $this->_cacheState;
    }

    /**
     * Get logger
     *
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * Get escaper
     *
     * @return \Magento\Framework\Escaper
     */
    public function getEscaper()
    {
        return $this->_escaper;
    }

    /**
     * Get filter manager
     *
     * @return \Magento\Framework\Filter\FilterManager
     */
    public function getFilterManager()
    {
        return $this->_filterManager;
    }

    /**
     * Get locale date.
     *
     * @return \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    public function getLocaleDate()
    {
        return $this->_localeDate;
    }

    /**
     * Lock guarded cache loader.
     *
     * @return LockGuardedCacheLoader
     * @since 102.0.2
     */
    public function getLockGuardedCacheLoader()
    {
        return $this->lockQuery;
    }
}
