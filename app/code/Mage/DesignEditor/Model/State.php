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
 * @category    Mage
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Design editor state model
 */
class Mage_DesignEditor_Model_State
{
    /**
     * Name of layout classes that will be used as main layout
     */
    const LAYOUT_NAVIGATION_CLASS_NAME = 'Mage_Core_Model_Layout';

    /**
     * Url model classes that will be used instead of Mage_Core_Model_Url in navigation vde modes
     */
    const URL_MODEL_NAVIGATION_MODE_CLASS_NAME = 'Mage_DesignEditor_Model_Url_NavigationMode';

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
     * @var Mage_Backend_Model_Session
     */
    protected $_backendSession;

    /**
     * @var Mage_Core_Model_Layout_Factory
     */
    protected $_layoutFactory;

    /**
     * @var Mage_DesignEditor_Model_Url_Factory
     */
    protected $_urlModelFactory;

    /**
     * Application Cache Manager
     *
     * @var Mage_Core_Model_Cache_Types
     */
    protected $_cacheTypes;

    /**
     * @var Mage_DesignEditor_Helper_Data
     */
    protected $_dataHelper;

    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Mage_Core_Model_App
     */
    protected $_application;

    /**
     * @param Mage_Backend_Model_Session $backendSession
     * @param Mage_Core_Model_Layout_Factory $layoutFactory
     * @param Mage_DesignEditor_Model_Url_Factory $urlModelFactory
     * @param Mage_Core_Model_Cache_Types $cacheTypes
     * @param Mage_DesignEditor_Helper_Data $dataHelper
     * @param Magento_ObjectManager $objectManager
     * @param Mage_Core_Model_App $application
     * @param Mage_DesignEditor_Model_Theme_Context $themeContext
     */
    public function __construct(
        Mage_Backend_Model_Session $backendSession,
        Mage_Core_Model_Layout_Factory $layoutFactory,
        Mage_DesignEditor_Model_Url_Factory $urlModelFactory,
        Mage_Core_Model_Cache_Types $cacheTypes,
        Mage_DesignEditor_Helper_Data $dataHelper,
        Magento_ObjectManager $objectManager,
        Mage_Core_Model_App $application,
        Mage_DesignEditor_Model_Theme_Context $themeContext
    ) {
        $this->_backendSession  = $backendSession;
        $this->_layoutFactory   = $layoutFactory;
        $this->_urlModelFactory = $urlModelFactory;
        $this->_cacheTypes      = $cacheTypes;
        $this->_dataHelper      = $dataHelper;
        $this->_objectManager   = $objectManager;
        $this->_application     = $application;
        $this->_themeContext    = $themeContext;
    }

    /**
     * Update system data for current VDE environment
     *
     * @param string $areaCode
     * @param Mage_Core_Controller_Request_Http $request
     */
    public function update($areaCode, Mage_Core_Controller_Request_Http $request)
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
     * @return Mage_DesignEditor_Model_State
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
     * Create url model instance that will be used instead of Mage_Core_Model_Url in navigation mode
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
                Mage_Core_Model_View_Design::XML_PATH_THEME_ID,
                $themeId
            );
            $this->_application->getConfig()->setNode(
                'default/' . Mage_Core_Model_View_Design::XML_PATH_THEME_ID,
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
            if ($this->_cacheTypes->isEnabled($cacheCode)) {
                $this->_cacheTypes->setEnabled($cacheCode, false);
            }
        }
    }
}
