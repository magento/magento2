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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Design editor state model
 */
class Mage_DesignEditor_Model_State
{
    /**#@+
     * Name of layout classes that will be used as main layout
     */
    const LAYOUT_DESIGN_CLASS_NAME = 'Mage_DesignEditor_Model_Layout';
    const LAYOUT_NAVIGATION_CLASS_NAME = 'Mage_Core_Model_Layout';
    /**#@-*/

    /**#@+
     * Url model classes that will be used instead of Mage_Core_Model_Url in different vde modes
     */
    const URL_MODEL_NAVIGATION_MODE_CLASS_NAME = 'Mage_DesignEditor_Model_Url_NavigationMode';
    const URL_MODEL_DESIGN_MODE_CLASS_NAME     = 'Mage_DesignEditor_Model_Url_DesignMode';
    /**#@-*/

    /**#@+
     * Import behaviors
     */
    const MODE_DESIGN     = 'design';
    const MODE_NAVIGATION = 'navigation';
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
     * @var Mage_Core_Model_Cache
     */
    protected $_cacheManager;

    /**
     * @var Mage_DesignEditor_Helper_Data
     */
    protected $_dataHelper;

    /**
     * @param Mage_Backend_Model_Session $backendSession
     * @param Mage_Core_Model_Layout_Factory $layoutFactory
     * @param Mage_DesignEditor_Model_Url_Factory $urlModelFactory
     * @param Mage_Core_Model_Cache $cacheManager
     * @param Mage_DesignEditor_Helper_Data $dataHelper
     */
    public function __construct(
        Mage_Backend_Model_Session $backendSession,
        Mage_Core_Model_Layout_Factory $layoutFactory,
        Mage_DesignEditor_Model_Url_Factory $urlModelFactory,
        Mage_Core_Model_Cache $cacheManager,
        Mage_DesignEditor_Helper_Data $dataHelper
    ) {
        $this->_backendSession  = $backendSession;
        $this->_layoutFactory   = $layoutFactory;
        $this->_urlModelFactory = $urlModelFactory;
        $this->_cacheManager    = $cacheManager;
        $this->_dataHelper      = $dataHelper;
    }

    public function update(
        $areaCode,
        Mage_Core_Controller_Request_Http $request,
        Mage_Core_Controller_Varien_ActionAbstract $controller
    ) {
        $handle = $request->getParam('handle', '');
        if (empty($handle)) {
            $mode = self::MODE_NAVIGATION;

            if (!$request->isAjax()) {
                $this->_backendSession->setData('vde_current_handle', $controller->getFullActionName());
                $this->_backendSession->setData('vde_current_url', $request->getPathInfo());
            }
        } else {
            $mode = self::MODE_DESIGN;
        }

        $this->_backendSession->setData('vde_current_mode', $mode);
        $this->_injectUrlModel($mode);
        $this->_injectLayout($mode, $areaCode);
        $this->_disableCache();
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
            case self::MODE_DESIGN:
                $this->_layoutFactory->createLayout(array('area' => $areaCode), self::LAYOUT_DESIGN_CLASS_NAME);
                break;
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
            case self::MODE_DESIGN:
                $this->_urlModelFactory->replaceClassName(self::URL_MODEL_DESIGN_MODE_CLASS_NAME);
                break;
            case self::MODE_NAVIGATION:
            default:
                $this->_urlModelFactory->replaceClassName(self::URL_MODEL_NAVIGATION_MODE_CLASS_NAME);
                break;
        }
    }

    /**
     * Disable some cache types in VDE mode
     */
    protected function _disableCache()
    {
        foreach ($this->_dataHelper->getDisabledCacheTypes() as $cacheCode) {
            $this->_cacheManager->banUse($cacheCode);
            $this->_cacheManager->cleanType($cacheCode);
        }
    }
}
