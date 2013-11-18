<?php

namespace Magento\Backend\Block\Template;

/**
 * Backend block template context
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
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Context extends \Magento\Core\Block\Template\Context
{
    /**
     * @var \Magento\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @var \Magento\Core\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @var \Magento\Math\Random
     */
    protected $mathRandom;

    /**
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\App\RequestInterface $request
     * @param \Magento\View\LayoutInterface $layout
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\UrlInterface $urlBuilder
     * @param \Magento\Core\Model\Translate $translator
     * @param \Magento\App\CacheInterface $cache
     * @param \Magento\View\DesignInterface $design
     * @param \Magento\Core\Model\Session $session
     * @param \Magento\Core\Model\Store\Config $storeConfig
     * @param \Magento\App\FrontController $frontController
     * @param \Magento\Core\Model\Factory\Helper $helperFactory
     * @param \Magento\View\Url $viewUrl
     * @param \Magento\View\ConfigInterface $viewConfig
     * @param \Magento\App\Cache\StateInterface $cacheState
     * @param \Magento\App\Dir $dirs
     * @param \Magento\Logger $logger
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\View\FileSystem $viewFileSystem
     * @param \Magento\View\TemplateEngineFactory $engineFactory
     * @param \Magento\AuthorizationInterface $authorization
     * @param \Magento\Core\Model\App $app
     * @param \Magento\App\State $appState
     * @param \Magento\Escaper $escaper
     * @param \Magento\Filter\FilterManager $filterManager
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Math\Random $mathRandom
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\App\RequestInterface $request,
        \Magento\View\LayoutInterface $layout,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\UrlInterface $urlBuilder,
        \Magento\Core\Model\Translate $translator,
        \Magento\App\CacheInterface $cache,
        \Magento\View\DesignInterface $design,
        \Magento\Core\Model\Session $session,
        \Magento\Core\Model\Store\Config $storeConfig,
        \Magento\App\FrontController $frontController,
        \Magento\Core\Model\Factory\Helper $helperFactory,
        \Magento\View\Url $viewUrl,
        \Magento\View\ConfigInterface $viewConfig,
        \Magento\App\Cache\StateInterface $cacheState,
        \Magento\App\Dir $dirs,
        \Magento\Logger $logger,
        \Magento\Filesystem $filesystem,
        \Magento\View\FileSystem $viewFileSystem,
        \Magento\View\TemplateEngineFactory $engineFactory,
        \Magento\AuthorizationInterface $authorization,
        \Magento\Core\Model\App $app,
        \Magento\App\State $appState,
        \Magento\Escaper $escaper,
        \Magento\Filter\FilterManager $filterManager,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\Math\Random $mathRandom
    ) {
        parent::__construct(
            $request,
            $layout,
            $eventManager,
            $urlBuilder,
            $translator,
            $cache,
            $design,
            $session,
            $storeConfig,
            $frontController,
            $helperFactory,
            $viewUrl,
            $viewConfig,
            $cacheState,
            $dirs,
            $logger,
            $filesystem,
            $viewFileSystem,
            $engineFactory,
            $app,
            $appState,
            $escaper,
            $filterManager,
            $locale
        );
        $this->_storeManager = $storeManager;
        $this->_authorization = $authorization;
        $this->_backendSession = $backendSession;
        $this->mathRandom = $mathRandom;
    }

    /**
     * Get store manager
     *
     * @return \Magento\Core\Model\StoreManager
     */
    public function getStoreManager()
    {
        return $this->_storeManager;
    }

    /**
     * Retrieve Authorization
     *
     * @return \Magento\AuthorizationInterface
     */
    public function getAuthorization()
    {
        return $this->_authorization;
    }

    /**
     * @return \Magento\Backend\Model\Session
     */
    public function getBackendSession()
    {
        return $this->_backendSession;
    }

    /**
     * @return \Magento\Core\Model\LocaleInterface
     */
    public function getLocale()
    {
        return $this->_locale;
    }

    /**
     * @return \Magento\Math\Random
     */
    public function getMathRandom()
    {
        return $this->mathRandom;
    }
}
