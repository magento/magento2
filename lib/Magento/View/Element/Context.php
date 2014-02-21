<?php
/**
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

namespace Magento\View\Element;

/**
 * Abstract block context object
 *
 * Will be used as block constructor modification point after release.
 * Important: Should not be modified by extension developers.
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Context implements \Magento\ObjectManager\ContextInterface
{
    /**
     * Request
     *
     * @var \Magento\App\RequestInterface
     */
    protected $_request;

    /**
     * Layout
     *
     * @var \Magento\View\LayoutInterface
     */
    protected $_layout;

    /**
     * Event manager
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * URL builder
     *
     * @var \Magento\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * Translator
     *
     * @var \Magento\TranslateInterface
     */
    protected $_translator;

    /**
     * Cache
     *
     * @var \Magento\App\CacheInterface
     */
    protected $_cache;

    /**
     * Design
     *
     * @var \Magento\View\DesignInterface
     */
    protected $_design;

    /**
     * Session
     *
     * @var \Magento\Session\SessionManagerInterface
     */
    protected $_session;

    /**
     * SID Resolver
     *
     * @var \Magento\Session\SidResolverInterface
     */
    protected $_sidResolver;

    /**
     * Store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_storeConfig;

    /**
     * Front controller
     *
     * @var \Magento\App\FrontController
     */
    protected $_frontController;

    /**
     * View URL
     *
     * @var \Magento\View\Url
     */
    protected $_viewUrl;

    /**
     * View config model
     *
     * @var \Magento\View\ConfigInterface
     */
    protected $_viewConfig;

    /**
     * Cache state
     *
     * @var \Magento\App\Cache\StateInterface
     */
    protected $_cacheState;

    /**
     * Logger
     *
     * @var \Magento\Logger
     */
    protected $_logger;

    /**
     * Application
     *
     * @var \Magento\Core\Model\App
     */
    protected $_app;

    /**
     * Escaper
     *
     * @var \Magento\Escaper
     */
    protected $_escaper;

    /**
     * Filter manager
     *
     * @var \Magento\Filter\FilterManager
     */
    protected $_filterManager;

    /**
     * Locale
     *
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale;

    /**
     * Constructor
     *
     * @param \Magento\App\RequestInterface $request
     * @param \Magento\View\LayoutInterface $layout
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\UrlInterface $urlBuilder
     * @param \Magento\TranslateInterface $translator
     * @param \Magento\App\CacheInterface $cache
     * @param \Magento\View\DesignInterface $design
     * @param \Magento\Session\SessionManagerInterface $session
     * @param \Magento\Session\SidResolverInterface $sidResolver
     * @param \Magento\Core\Model\Store\Config $storeConfig
     * @param \Magento\App\FrontController $frontController
     * @param \Magento\View\Url $viewUrl
     * @param \Magento\View\ConfigInterface $viewConfig
     * @param \Magento\App\Cache\StateInterface $cacheState
     * @param \Magento\Logger $logger
     * @param \Magento\Core\Model\App $app
     * @param \Magento\Escaper $escaper
     * @param \Magento\Filter\FilterManager $filterManager
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\App\RequestInterface $request,
        \Magento\View\LayoutInterface $layout,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\UrlInterface $urlBuilder,
        \Magento\TranslateInterface $translator,
        \Magento\App\CacheInterface $cache,
        \Magento\View\DesignInterface $design,
        \Magento\Session\SessionManagerInterface $session,
        \Magento\Session\SidResolverInterface $sidResolver,
        \Magento\Core\Model\Store\Config $storeConfig,
        \Magento\App\FrontController $frontController,
        \Magento\View\Url $viewUrl,
        \Magento\View\ConfigInterface $viewConfig,
        \Magento\App\Cache\StateInterface $cacheState,
        \Magento\Logger $logger,
        \Magento\Core\Model\App $app,
        \Magento\Escaper $escaper,
        \Magento\Filter\FilterManager $filterManager,
        \Magento\Core\Model\LocaleInterface $locale,
        array $data = array()
    ) {
        $this->_request         = $request;
        $this->_layout          = $layout;
        $this->_eventManager    = $eventManager;
        $this->_urlBuilder      = $urlBuilder;
        $this->_translator      = $translator;
        $this->_cache           = $cache;
        $this->_design          = $design;
        $this->_session         = $session;
        $this->_sidResolver     = $sidResolver;
        $this->_storeConfig     = $storeConfig;
        $this->_frontController = $frontController;
        $this->_viewUrl         = $viewUrl;
        $this->_viewConfig      = $viewConfig;
        $this->_cacheState      = $cacheState;
        $this->_logger          = $logger;
        $this->_app             = $app;
        $this->_escaper         = $escaper;
        $this->_filterManager   = $filterManager;
        $this->_locale          = $locale;
    }

    /**
     * Get cache
     *
     * @return \Magento\App\CacheInterface
     */
    public function getCache()
    {
        return $this->_cache;
    }

    /**
     * Get design package
     *
     * @return \Magento\View\DesignInterface
     */
    public function getDesignPackage()
    {
        return $this->_design;
    }

    /**
     * Get event manager
     *
     * @return \Magento\Event\ManagerInterface
     */
    public function getEventManager()
    {
        return $this->_eventManager;
    }

    /**
     * Get front controller
     *
     * @return \Magento\App\FrontController
     */
    public function getFrontController()
    {
        return $this->_frontController;
    }

    /**
     * Get layout
     *
     * @return \Magento\View\LayoutInterface
     */
    public function getLayout()
    {
        return $this->_layout;
    }

    /**
     * Get request
     *
     * @return \Magento\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Get session
     *
     * @return \Magento\Session\SessionManagerInterface
     */
    public function getSession()
    {
        return $this->_session;
    }

    /**
     * Get SID resolver
     *
     * @return \Magento\Session\SidResolverInterface
     */
    public function getSidResolver()
    {
        return $this->_sidResolver;
    }

    /**
     * Get store config
     *
     * @return \Magento\Core\Model\Store\Config
     */
    public function getStoreConfig()
    {
        return $this->_storeConfig;
    }

    /**
     * Get translator
     *
     * @return \Magento\TranslateInterface
     */
    public function getTranslator()
    {
        return $this->_translator;
    }

    /**
     * Get URL builder
     *
     * @return \Magento\UrlInterface
     */
    public function getUrlBuilder()
    {
        return $this->_urlBuilder;
    }

    /**
     * Get view URL
     *
     * @return \Magento\View\Url
     */
    public function getViewUrl()
    {
        return $this->_viewUrl;
    }

    /**
     * Get view config
     *
     * @return \Magento\View\ConfigInterface
     */
    public function getViewConfig()
    {
        return $this->_viewConfig;
    }

    /**
     * Get cache state
     *
     * @return \Magento\App\Cache\StateInterface
     */
    public function getCacheState()
    {
        return $this->_cacheState;
    }

    /**
     * Get logger
     *
     * @return \Magento\Logger
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * Get application
     *
     * @return \Magento\Core\Model\App
     */
    public function getApp()
    {
        return $this->_app;
    }

    /**
     * Get escaper
     *
     * @return \Magento\Escaper
     */
    public function getEscaper()
    {
        return $this->_escaper;
    }

    /**
     * Get filter manager
     *
     * @return \Magento\Filter\FilterManager
     */
    public function getFilterManager()
    {
        return $this->_filterManager;
    }

    /**
     * Get locale
     *
     * @return \Magento\Core\Model\LocaleInterface
     */
    public function getLocale()
    {
        return $this->_locale;
    }
}
