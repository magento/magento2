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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Design service model
 */
class Mage_Core_Model_View_Service
{
    /**
     * Scope separator
     */
    const SCOPE_SEPARATOR = '::';

    /**
     * @var Mage_Core_Model_App_State
     */
    protected $_appState;

    /**
     * @var Mage_Core_Model_View_Design_Proxy
     */
    private $_design;

    /**
     * @var Mage_Core_Model_Theme_FlyweightFactory
     */
    protected $_themeFactory;

    /**
     * View files system model
     *
     *
     * @param Mage_Core_Model_App_State $appState
     * @param Mage_Core_Model_View_Design_Proxy $design
     * @param Mage_Core_Model_Theme_FlyweightFactory $themeFactory
     */
    public function __construct(
        Mage_Core_Model_App_State $appState,
        Mage_Core_Model_View_Design_Proxy $design,
        Mage_Core_Model_Theme_FlyweightFactory $themeFactory
    ) {
        $this->_appState = $appState;
        $this->_design = $design;
        $this->_themeFactory = $themeFactory;
    }

    /**
     * Identify file scope if it defined in file name and override 'module' parameter in $params array
     *
     * It accepts $fileId e.g. Mage_Core::prototype/magento.css and splits it to module part and path part.
     * Then sets module path to $params['module'] and returns path part.
     *
     * @param string $fileId
     * @param array &$params
     * @return string
     * @throws Magento_Exception
     */
    public function extractScope($fileId, array &$params)
    {
        if (preg_match('/\.\//', str_replace('\\', '/', $fileId))) {
            throw new Magento_Exception("File name '{$fileId}' is forbidden for security reasons.");
        }
        if (strpos($fileId, self::SCOPE_SEPARATOR) === false) {
            $file = $fileId;
        } else {
            $fileId = explode(self::SCOPE_SEPARATOR, $fileId);
            if (empty($fileId[0])) {
                throw new Magento_Exception('Scope separator "::" cannot be used without scope identifier.');
            }
            $params['module'] = $fileId[0];
            $file = $fileId[1];
        }
        return $file;
    }

    /**
     * Verify whether we should work with files
     *
     * @return bool
     */
    public function isViewFileOperationAllowed()
    {
        return $this->getAppMode() != Mage_Core_Model_App_State::MODE_PRODUCTION;
    }

    /**
     * Return whether developer mode is turned on
     *
     * @return string
     */
    public function getAppMode()
    {
        return $this->_appState->getMode();
    }

    /**
     * Return directory for theme files publication
     *
     * @return string
     */
    public function getPublicDir()
    {
        return Mage::getBaseDir(Mage_Core_Model_Dir::STATIC_VIEW);
    }

    /**
     * Update required parameters with default values if custom not specified
     *
     * @param array $params
     * @return Mage_Core_Model_View_Design
     */
    public function updateDesignParams(array &$params)
    {
        $defaults = $this->_design->getDesignParams();

        // Set area
        if (empty($params['area'])) {
            $params['area'] = $defaults['area'];
        }

        // Set themeModel
        $theme = null;
        $area = $params['area'];
        if (!empty($params['themeId'])) {
            $theme = $params['themeId'];
        } elseif (!empty($params['package']) && isset($params['theme'])) {
            $themePath = $params['package'] . '/' . $params['theme'];
            $theme = $themePath;
        } elseif (empty($params['themeModel']) && $area !== $defaults['area']) {
            $theme = $this->_design->getConfigurationDesignTheme($area);
        }

        if ($theme) {
            $params['themeModel'] = $this->_themeFactory->create($theme, $area);
        } elseif (empty($params['themeModel'])) {
            $params['themeModel'] = $defaults['themeModel'];
        }


        // Set module
        if (!array_key_exists('module', $params)) {
            $params['module'] = false;
        }

        // Set locale
        if (empty($params['locale'])) {
            $params['locale'] = $defaults['locale'];
        }
        return $this;
    }
}
