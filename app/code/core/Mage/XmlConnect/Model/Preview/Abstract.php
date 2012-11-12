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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Device preview model abstract
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_XmlConnect_Model_Preview_Abstract extends Varien_Object
{
    /**
     * Current active tab according preview action
     *
     * @var bool|string
     */
    private $_activeTab = false;

    /**
     * Category item tint color styles
     *
     * @var string
     */
    protected $_categoryItemTintColor = '';

    /**
     * Current loaded application model
     *
     * @var Mage_XmlConnect_Model_Application|null
     */
    protected $_appModel = null;

    /**
     * Internal constructor not depended on params.
     * It's used for application object initialization
     *
     * @return null
     */
    final function _construct()
    {
        parent::_construct();
        $this->setApplicationModel();
    }

    /**
     * Setter for current loaded application model
     *
     * @return Mage_XmlConnect_Model_Preview_Abstract
     */
    protected function setApplicationModel()
    {
        if ($this->_appModel === null) {
            $this->_appModel = Mage::helper('Mage_XmlConnect_Helper_Data')->getApplication();
        }
        return $this;
    }


    /**
     * Getter for current loaded application model
     *
     * @return Mage_XmlConnect_Model_Application
     */
    public function getApplicationModel()
    {
        return $this->setApplicationModel()->_appModel;
    }

    /**
     * Set active tab
     *
     * @param string $tab
     * @return Mage_XmlConnect_Block_Adminhtml_Mobile_Preview_Tabitems
     */
    public function setActiveTab($tab)
    {
        $this->_activeTab = $tab;
        return $this;
    }

    /**
     * Check is exists a tab in a config array
     *
     * @param string $tabAction tab action name
     * @return bool
     */
    public function isItemExists($tabAction)
    {
        $tabs = $this->getTabItems();
        return (bool) isset($tabs[$tabAction]);
    }

    /**
     * Collect tab items array
     *
     * @return array
     */
    public function getTabItems()
    {
        $items = array();
        $model = $this->getApplicationModel();
        $tabs = $model->getEnabledTabsArray();
        $tabLimit = (int) Mage::getStoreConfig('xmlconnect/devices/'.strtolower($model->getType()).'/tab_limit');
        $showedTabs = 0;
        foreach ($tabs as $tab) {
            if (++$showedTabs > $tabLimit) {
                break;
            }
            $items[$tab->action] = array(
                'label' => Mage::helper('Mage_XmlConnect_Helper_Data')->getTabLabel($tab->action),
                'image' => $tab->image,
                'action' => $tab->action,
                'active' => strtolower($tab->action) == strtolower($this->_activeTab),
            );
        }
        return $items;
    }

    /**
     * Prepare config data
     * Implement set "conf" data as magic method
     *
     * @param array $conf
     */
    public function setConf($conf)
    {
        if (!is_array($conf)) {
            $conf = array();
        }
        $tabs = isset($conf['tabBar']['tabs']) ? $conf['tabBar']['tabs'] : false;
        if ($tabs !== false) {
            foreach ($tabs->getEnabledTabs() as $tab) {
                $tab = (array) $tab;
                $conf['tabBar'][$tab['action']]['label'] = $tab['label'];
                $conf['tabBar'][$tab['action']]['image'] = $tab['image'];
            }
        }
        $this->setData('conf', $conf);
    }

   /**
    * Get preview images url
    *
    * @param string $name file name
    * @return string
    */
    public function getPreviewImagesUrl($name = '')
    {
        return Mage::helper('Mage_XmlConnect_Helper_Image')->getSkinImagesUrl('mobile_preview/' . $name);
    }

    /**
     * Get application banner image url
     *
     * @return string
     */
    abstract public function getBannerImage();

    /**
     * Get background image url
     *
     * @return string
     */
    abstract public function getBackgroundImage();

    /**
     * Get font info from a config
     *
     * @param string $path
     * @return string
     */
    public function getConfigFontInfo($path)
    {
        return $this->getData('conf/fonts/' . $path);
    }

    /**
     * Get icon logo url
     *
     * @return string
     */
    public function getLogoUrl()
    {
        $configPath = 'conf/navigationBar/icon';
        if ($this->getData($configPath)) {
            return $this->getData($configPath);
        } else {
            return $this->getPreviewImagesUrl('smallIcon.png');
        }
    }

    /**
     * Get category item tint color styles
     *
     * @return string
     */
    public function getCategoryItemTintColor()
    {
        if (!strlen($this->_categoryItemTintColor)) {
            $percent = 0.4;
            $mask   = 255;

            $hex    = str_replace('#', '', $this->getData('conf/categoryItem/tintColor'));
            $hex2   = '';
            $_rgb   = array();

            $hexChars = '[a-fA-F0-9]';

            if (preg_match("/^($hexChars{2})($hexChars{2})($hexChars{2})$/", $hex, $rgb)) {
                $_rgb = array(hexdec($rgb[1]), hexdec($rgb[2]), hexdec($rgb[3]));
            } elseif (preg_match("/^($hexChars)($hexChars)($hexChars)$/", $hex, $rgb)) {
                $_rgb = array(hexdec($rgb[1] . $rgb[1]), hexdec($rgb[2] . $rgb[2]), hexdec($rgb[3] . $rgb[3]));
            }

            for ($i = 0; $i < 3; $i++) {
                if (!isset($_rgb[$i])) {
                    $_rgb[$i] = 0;
                }
                $_rgb[$i] = round($_rgb[$i] * $percent) + round($mask * (1 - $percent));
                if ($_rgb[$i] > 255) {
                    $_rgb[$i] = 255;
                }
                $hex_digit = dechex($_rgb[$i]);
                if (strlen($hex_digit) == 1) {
                    $hex_digit = "0" . $hex_digit;
                }
                $hex2 .= $hex_digit;
            }

            if ($hex && $hex2) {
                // for IE
                $this->_categoryItemTintColor .= "filter: progid:DXImageTransform.Microsoft.gradient";
                $this->_categoryItemTintColor .= "(startColorstr='#" . $hex2 . "', endColorstr='#" . $hex . "');";
                // for webkit browsers
                $this->_categoryItemTintColor .= "background:-webkit-gradient";
                $this->_categoryItemTintColor .= "(linear, left top, left bottom,";
                $this->_categoryItemTintColor .= " from(#" . $hex2 . "), to(#" . $hex . "));";
                // for firefox
                $this->_categoryItemTintColor .= "background:-moz-linear-gradient";
                $this->_categoryItemTintColor .= "(top, #" . $hex2 . ", #" . $hex . ");";
            }
        }
        return $this->_categoryItemTintColor;
    }
}
