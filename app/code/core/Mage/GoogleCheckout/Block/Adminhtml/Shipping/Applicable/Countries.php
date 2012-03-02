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
 * @package     Mage_GoogleCheckout
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_GoogleCheckout_Block_Adminhtml_Shipping_Applicable_Countries
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected $_addRowButtonHtml = array();
    protected $_removeRowButtonHtml = array();

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html = parent::_getElementHtml($element);
        $html .= $this->_appendJs($element);
        return $html;
    }

    protected function _appendJs($element)
    {
        $elId = $element->getHtmlId();
        $childId = str_replace('sallowspecific', 'specificcountry', $elId);
        $html = "<script type='text/javascript'>
        var dwvie = function ()
        {
            var valueSelectId = '{$elId}';
            var elementToDisableId = '{$childId}';

            var source = $(valueSelectId);
            var target = $(elementToDisableId);

            if (source.options[source.selectedIndex].value == '0') {
                target.disabled = true;
            } else {
                target.disabled = false;
            }
        }

        Event.observe('{$elId}', 'change', dwvie);
        Event.observe(window, 'load', dwvie);
        </script>";
        return $html;
    }
}
