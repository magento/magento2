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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog Custom Options Config Renderer
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Form_Renderer_Config_DateFieldsOrder extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $_options = array(
            'd' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Day'),
            'm' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Month'),
            'y' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Year')
        );

        $element->setValues($_options)
            ->setStyle('width:70px;')
            ->setName($element->getName() . '[]');
        if ($element->getValue()) {
            $values = explode(',', $element->getValue());
        } else {
            $values = array();
        }

        $_parts = array();
        $_parts[] = $element->setValue(isset($values[0]) ? $values[0] : null)->getElementHtml();
        $_parts[] = $element->setValue(isset($values[1]) ? $values[1] : null)->getElementHtml();
        $_parts[] = $element->setValue(isset($values[2]) ? $values[2] : null)->getElementHtml();

        return implode(' / ', $_parts);
    }
}
