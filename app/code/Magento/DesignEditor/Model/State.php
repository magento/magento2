<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Model;

/**
 * Design editor state model
 */
class State
{
    /**
     * Url model classes that will be used instead of \Magento\Framework\UrlInterface in navigation vde modes
     */
    const URL_MODEL_NAVIGATION_MODE_CLASS_NAME = 'Magento\DesignEditor\Model\Url\NavigationMode';

    /**
     * Import behaviors
     */
    const MODE_NAVIGATION = 'navigation';

    /**#@+
     * Session keys
     */
    const CURRENT_URL_SESSION_KEY = 'vde_current_url';

    const CURRENT_MODE_SESSION_KEY = 'vde_current_mode';

    /**#@-*/

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * @var AreaEmulator
     */
    protected $_areaEmulator;

    /**
     * @var \Magento\DesignEditor\Model\Url\Factory
     */
    protected $_urlModelFactory;

    /**
     * Application Cache Manager
     *
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    protected $_cacheState;

    /**
     * @var \Magento\DesignEditor\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_configuration;

    /**
     * Mutable Config
     *
     * @var \Magento\Framework\App\Config\MutableScopeConfigInterface
     */
    protected $_mutableConfig;

    /**
     * @param \Magento\Backend\Model\Session $backendSession
     * @param AreaEmulator $areaEmulator
     * @param Url\Factory $urlModelFactory
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param \Magento\DesignEditor\Helper\Data $dataHelper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $configuration
     * @param Theme\Context $themeContext
     * @param \Magento\Framework\App\Config\MutableScopeConfigInterface $mutableConfig
     */
    public function __construct(
        \Magento\Backend\Model\Session $backendSession,
        AreaEmulator $areaEmulator,
        \Magento\DesignEditor\Model\Url\Factory $urlModelFactory,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Magento\DesignEditor\Helper\Data $dataHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $configuration,
        \Magento\DesignEditor\Model\Theme\Context $themeContext,
        \Magento\Framework\App\Config\MutableScopeConfigInterface $mutableConfig
    ) {
        $this->_backendSession = $backendSession;
        $this->_areaEmulator = $areaEmulator;
        $this->_urlModelFactory = $urlModelFactory;
        $this->_cacheState = $cacheState;
        $this->_dataHelper = $dataHelper;
        $this->_objectManager = $objectManager;
        $this->_configuration = $configuration;
        $this->_themeContext = $themeContext;
        $this->_mutableConfig = $mutableConfig;
    }

    /**
     * Update system data for current VDE environment
     *
     * @param string $areaCode
     * @param \Magento\Framework\App\RequestInterface $request
     * @return void
     */
    public function update($areaCode, \Magento\Framework\App\RequestInterface $request)
    {
        $mode = $request->getAlias('editorMode') ?: self::MODE_NAVIGATION;
        $this->_themeContext->setEditableThemeById($request->getAlias('themeId'));

        if (!$request->isAjax()) {
            $this->_backendSession->setData(self::CURRENT_URL_SESSION_KEY, $request->getPathInfo());
            $this->_backendSession->setData(self::CURRENT_MODE_SESSION_KEY, $mode);
        }
        $this->_injectUrlModel($mode);
        $this->_emulateArea($mode, $areaCode);
        $this->_setTheme();
        $this->_disableCache();
    }

    /**
     * Reset VDE state data
     *
     * @return $this
     */
    public function reset()
    {
        $this->_backendSession->unsetData(self::CURRENT_URL_SESSION_KEY)->unsetData(self::CURRENT_MODE_SESSION_KEY);
        $this->_themeContext->reset();
        return $this;
    }

    /**
     * Emulate environment of an area
     *
     * @param string $mode
     * @param string $areaCode
     * @return void
     */
    protected function _emulateArea($mode, $areaCode)
    {
        switch ($mode) {
            case self::MODE_NAVIGATION:
            default:
                $this->_areaEmulator->emulateLayoutArea($areaCode);
                break;
        }
    }

    /**
     * Create url model instance that will be used instead of \Magento\Framework\UrlInterface in navigation mode
     *
     * @param string $mode
     * @return void
     */
    protected function _injectUrlModel($mode)
    {
        switch ($mode) {
            case self::MODE_NAVIGATION:
            default:
                $this->_urlModelFactory->replaceClassName(self::URL_MODEL_NAVIGATION_MODE_CLASS_NAME);
                break;
        }
    }

    /**
     * Set current VDE theme
     *
     * @return void
     */
    protected function _setTheme()
    {
        if ($this->_themeContext->getEditableTheme()) {
            $themeId = $this->_themeContext->getVisibleTheme()->getId();
            $this->_mutableConfig->setValue(
                \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID,
                $themeId,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $this->_configuration->setValue(\Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID, $themeId);
        }
    }

    /**
     * Disable some cache types in VDE mode
     *
     * @return void
     */
    protected function _disableCache()
    {
        foreach ($this->_dataHelper->getDisabledCacheTypes() as $cacheCode) {
            if ($this->_cacheState->isEnabled($cacheCode)) {
                $this->_cacheState->setEnabled($cacheCode, false);
            }
        }
    }
}
