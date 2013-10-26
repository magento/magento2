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
 * @category    Magento
 * @package     Magento_DesignEditor
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Design editor state model
 */
namespace Magento\DesignEditor\Model;

class State
{
    /**
     * Name of layout classes that will be used as main layout
     */
    const LAYOUT_NAVIGATION_CLASS_NAME = 'Magento\Core\Model\Layout';

    /**
     * Url model classes that will be used instead of \Magento\Core\Model\Url in navigation vde modes
     */
    const URL_MODEL_NAVIGATION_MODE_CLASS_NAME = 'Magento\DesignEditor\Model\Url\NavigationMode';

    /**
     * Import behaviors
     */
    const MODE_NAVIGATION = 'navigation';

    /**#@+
     * Session keys
     */
    const CURRENT_URL_SESSION_KEY    = 'vde_current_url';
    const CURRENT_MODE_SESSION_KEY   = 'vde_current_mode';
    /**#@-*/

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * @var \Magento\Core\Model\Layout\Factory
     */
    protected $_layoutFactory;

    /**
     * @var \Magento\DesignEditor\Model\Url\Factory
     */
    protected $_urlModelFactory;

    /**
     * Application Cache Manager
     *
     * @var \Magento\Core\Model\Cache\StateInterface
     */
    protected $_cacheState;

    /**
     * @var \Magento\DesignEditor\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Core\Model\App
     */
    protected $_application;

    /**
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Core\Model\Layout\Factory $layoutFactory
     * @param \Magento\DesignEditor\Model\Url\Factory $urlModelFactory
     * @param \Magento\Core\Model\Cache\StateInterface $cacheState
     * @param \Magento\DesignEditor\Helper\Data $dataHelper
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\Core\Model\App $application
     * @param \Magento\DesignEditor\Model\Theme\Context $themeContext
     */
    public function __construct(
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Core\Model\Layout\Factory $layoutFactory,
        \Magento\DesignEditor\Model\Url\Factory $urlModelFactory,
        \Magento\Core\Model\Cache\StateInterface $cacheState,
        \Magento\DesignEditor\Helper\Data $dataHelper,
        \Magento\ObjectManager $objectManager,
        \Magento\Core\Model\App $application,
        \Magento\DesignEditor\Model\Theme\Context $themeContext
    ) {
        $this->_backendSession  = $backendSession;
        $this->_layoutFactory   = $layoutFactory;
        $this->_urlModelFactory = $urlModelFactory;
        $this->_cacheState      = $cacheState;
        $this->_dataHelper      = $dataHelper;
        $this->_objectManager   = $objectManager;
        $this->_application     = $application;
        $this->_themeContext    = $themeContext;
    }

    /**
     * Update system data for current VDE environment
     *
     * @param string $areaCode
     * @param \Magento\App\RequestInterface $request
     */
    public function update($areaCode, \Magento\App\RequestInterface $request)
    {
        $mode = $request->getAlias('editorMode') ?: self::MODE_NAVIGATION;
        $this->_themeContext->setEditableThemeById($request->getAlias('themeId'));

        if (!$request->isAjax()) {
            $this->_backendSession->setData(self::CURRENT_URL_SESSION_KEY, $request->getPathInfo());
            $this->_backendSession->setData(self::CURRENT_MODE_SESSION_KEY, $mode);
        }
        $this->_injectUrlModel($mode);
        $this->_injectLayout($mode, $areaCode);
        $this->_setTheme();
        $this->_disableCache();
    }

    /**
     * Reset VDE state data
     *
     * @return \Magento\DesignEditor\Model\State
     */
    public function reset()
    {
        $this->_backendSession->unsetData(self::CURRENT_URL_SESSION_KEY)
            ->unsetData(self::CURRENT_MODE_SESSION_KEY);
        $this->_themeContext->reset();
        return $this;
    }

    /**
     * Create layout instance that will be used as main layout for whole system
     *
     * @param string $mode
     * @param string $areaCode
     */
    protected function _injectLayout($mode, $areaCode)
    {
        switch ($mode) {
            case self::MODE_NAVIGATION:
            default:
                $this->_layoutFactory->createLayout(array('area' => $areaCode), self::LAYOUT_NAVIGATION_CLASS_NAME);
                break;
        }
    }

    /**
     * Create url model instance that will be used instead of \Magento\Core\Model\Url in navigation mode
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
     */
    protected function _setTheme()
    {
        if ($this->_themeContext->getEditableTheme()) {
            $themeId = $this->_themeContext->getVisibleTheme()->getId();
            $this->_application->getStore()->setConfig(
                \Magento\Core\Model\View\Design::XML_PATH_THEME_ID,
                $themeId
            );
            $this->_application->getConfig()->setValue(
                \Magento\Core\Model\View\Design::XML_PATH_THEME_ID,
                $themeId
            );
        }
    }

    /**
     * Disable some cache types in VDE mode
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
