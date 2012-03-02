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
 * XmlConnect tabs form element
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Adminhtml_Mobile_Form_Element_Tabs
    extends Varien_Data_Form_Element_Text
{
    /**
     * Generate application tabs html
     *
     * @return string
     */
    public function getHtml()
    {
        if ((bool)Mage::getSingleton('Mage_Adminhtml_Model_Session')->getNewApplication()) {
            return '';
        }

        $blockClassName = Mage::getConfig()->getBlockClassName('Mage_Adminhtml_Block_Template');
        $block = new $blockClassName;
        $device = Mage::helper('Mage_XmlConnect_Helper_Data')->getDeviceType();
        if (array_key_exists($device, Mage::helper('Mage_XmlConnect_Helper_Data')->getSupportedDevices())) {
            $template = 'Mage_XmlConnect::form/element/app_tabs_' . strtolower($device) . '.phtml';
        } else {
            Mage::throwException(
                $this->__('Device doesn\'t recognized. Unable to load a template.')
            );
        }

        $block->setTemplate($template);
        $tabs = Mage::getModel('Mage_XmlConnect_Model_Tabs', $this->getValue());
        $block->setTabs($tabs);
        $block->setName($this->getName());
        return $block->toHtml();
    }
}
