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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * XmlConnect Tabs model
 *
 * @category    Mage
 * @package     Mage_Xmlconnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Model_Tabs
{
    /**
     * Store enabled application design tabs
     *
     * @var array
     */
    protected $_enabledTabs = array();

    /**
     * Store disabled application design tabs
     *
     * @var array
     */
    protected $_disabledTabs = array();

    /**
     * Set enabled and disabled application tabs
     *
     * @param string $data
     */
    public function __construct($data)
    {
        $this->_enabledTabs = Mage::helper('Mage_XmlConnect_Helper_Data')->getDefaultApplicationDesignTabs();
        if (is_string($data)) {
            $data = json_decode($data);
            if (is_object($data)) {
                if (isset($data->enabledTabs)) {
                    $this->_enabledTabs = $data->enabledTabs;
                }
                if (isset($data->disabledTabs)) {
                    $this->_disabledTabs = $data->disabledTabs;
                }
            }
        }
        $this->_translateLabel($this->_enabledTabs);
        $this->_translateLabel($this->_disabledTabs);
    }

    /**
     * Translate Label fields
     *
     * @param array &$tabItems
     * @return Mage_XmlConnect_Model_Tabs
     */
    protected function _translateLabel(&$tabItems)
    {
        if (is_array($tabItems)) {
            foreach ($tabItems as $id => $tab) {
                $tempTab = $tabItems[$id];

                if (is_array($tab)) {
                    if (isset($tab['label'])) {
                        $tempTab['label'] = Mage::helper('Mage_XmlConnect_Helper_Data')->getTabLabel($tab['action']);
                    } else {
                        $tempTab['label'] = '';
                    }
                } else {
                    if (isset($tab->label)) {
                       $tempTab->label = Mage::helper('Mage_XmlConnect_Helper_Data')->getTabLabel($tab->action);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Getter for enabled tabs
     *
     * @return array
     */
    public function getEnabledTabs()
    {
        return $this->_enabledTabs;
    }

    /**
     * Getter for disabled tabs
     *
     * @return array
     */
    public function getDisabledTabs()
    {
        return $this->_disabledTabs;
    }

    /**
     * Collect tabs with images
     *
     * @return array
     */
    public function getRenderTabs()
    {
        return $this->_enabledTabs;
    }
}
