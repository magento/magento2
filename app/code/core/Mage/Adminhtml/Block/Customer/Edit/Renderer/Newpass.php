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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer new password field renderer
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Customer_Edit_Renderer_Newpass
    extends Mage_Core_Block_Template
    implements Varien_Data_Form_Element_Renderer_Interface
{

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '<tr>';
        $html.= '<td class="label">'.$element->getLabelHtml().'</td>';
        $html.= '<td class="value">'.$element->getElementHtml().'</td>';
        $html.= '</tr>'."\n";
        $html.= '<tr>';
        $html.= '<td class="label"><label>&nbsp;</label></td>';
        $html.= '<td class="value">'.Mage::helper('Mage_Customer_Helper_Data')->__('or').'</td>';
        $html.= '</tr>'."\n";
        $html.= '<tr>';
        $html.= '<td class="label"><label>&nbsp;</label></td>';
        $html.= '<td class="value"><input type="checkbox" id="account-send-pass" name="'.$element->getName().'" value="auto" onclick="setElementDisable(\''.$element->getHtmlId().'\', this.checked)"/>&nbsp;';
        $html.= '<label for="account-send-pass">'.Mage::helper('Mage_Customer_Helper_Data')->__('Send auto-generated password').'</label></td>';
        $html.= '</tr>'."\n";

        return $html;
    }

}
