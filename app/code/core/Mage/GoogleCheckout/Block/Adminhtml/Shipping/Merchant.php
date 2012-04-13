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

class Mage_GoogleCheckout_Block_Adminhtml_Shipping_Merchant
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected $_addRowButtonHtml = array();
    protected $_removeRowButtonHtml = array();

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);

        $html = '<div id="merchant_allowed_methods_template" style="display:none">';
        $html .= $this->_getRowTemplateHtml();
        $html .= '</div>';

        $html .= '<ul id="merchant_allowed_methods_container">';
        if ($this->_getValue('method')) {
            foreach ($this->_getValue('method') as $i => $f) {
                if ($i) {
                    $html .= $this->_getRowTemplateHtml($i);
                }
            }
        }
        $html .= '</ul>';
        $html .= $this->_getAddRowButtonHtml('merchant_allowed_methods_container',
            'merchant_allowed_methods_template', $this->__('Add Shipping Method'));

        return $html;
    }

    /**
     * Retrieve html template for shipping method row
     *
     * @param int $rowIndex
     * @return string
     */
    protected function _getRowTemplateHtml($rowIndex = 0)
    {
        $html = '<li>';
        $html .= '<select name="' . $this->getElement()->getName() . '[method][]" ' . $this->_getDisabled() . '>';
        $html .= '<option value="">' . $this->__('* Select shipping method') . '</option>';

        foreach ($this->getShippingMethods() as $carrierCode => $carrier) {
            $html .= '<optgroup label="' . $this->escapeHtml($carrier['title'])
                . '" style="border-top:solid 1px black; margin-top:3px;">';

            foreach ($carrier['methods'] as $methodCode => $method) {
                $code = $carrierCode . '/' . $methodCode;
                $html .= '<option value="' . $this->escapeHtml($code) . '" '
                    . $this->_getSelected('method/' . $rowIndex, $code)
                    . ' style="background:white;">' . $this->escapeHtml($method['title']) . '</option>';
            }
            $html .= '</optgroup>';
        }
        $html .= '</select>';

        $html .= '<div style="margin:5px 0 10px;">';
        $html .= '<label>' . $this->__('Default price:') . '</label> ';
        $html .= '<input class="input-text" style="width:70px;" name="'
            . $this->getElement()->getName() . '[price][]" value="'
            . $this->_getValue('price/' . $rowIndex) . '" ' . $this->_getDisabled() . '/> ';

        $html .= $this->_getRemoveRowButtonHtml();
        $html .= '</div>';
        $html .= '</li>';

        return $html;
    }

    protected function getShippingMethods()
    {
        if (!$this->hasData('shipping_methods')) {
            $website = $this->getRequest()->getParam('website');
            $store   = $this->getRequest()->getParam('store');

            $storeId = null;
            if (!is_null($website)) {
                $storeId = Mage::getModel('Mage_Core_Model_Website')
                    ->load($website, 'code')
                    ->getDefaultGroup()
                    ->getDefaultStoreId();
            } elseif (!is_null($store)) {
                $storeId = Mage::getModel('Mage_Core_Model_Store')
                    ->load($store, 'code')
                    ->getId();
            }

            $methods = array();
            $carriers = Mage::getSingleton('Mage_Shipping_Model_Config')->getActiveCarriers($storeId);
            foreach ($carriers as $carrierCode=>$carrierModel) {
                if (!$carrierModel->isActive()) {
                    continue;
                }
                $carrierMethods = $carrierModel->getAllowedMethods();
                if (!$carrierMethods) {
                    continue;
                }
                $carrierTitle = Mage::getStoreConfig('carriers/' . $carrierCode . '/title', $storeId);
                $methods[$carrierCode] = array(
                    'title'   => $carrierTitle,
                    'methods' => array(),
                );
                foreach ($carrierMethods as $methodCode=>$methodTitle) {
                    $methods[$carrierCode]['methods'][$methodCode] = array(
                        'title' => '[' . $carrierCode . '] ' . $methodTitle,
                    );
                }
            }
            $this->setData('shipping_methods', $methods);
        }
        return $this->getData('shipping_methods');
    }

    protected function _getDisabled()
    {
        return $this->getElement()->getDisabled() ? ' disabled' : '';
    }

    protected function _getValue($key)
    {
        return $this->getElement()->getData('value/' . $key);
    }

    protected function _getSelected($key, $value)
    {
        return $this->getElement()->getData('value/' . $key) == $value ? 'selected="selected"' : '';
    }

    protected function _getAddRowButtonHtml($container, $template, $title='Add')
    {
        if (!isset($this->_addRowButtonHtml[$container])) {
            $this->_addRowButtonHtml[$container] = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Widget_Button')
                    ->setType('button')
                    ->setClass('add ' . $this->_getDisabled())
                    ->setLabel($this->__($title))
                    ->setOnClick("Element.insert($('" . $container . "'), {bottom: $('" . $template . "').innerHTML})")
                    ->setDisabled($this->_getDisabled())
                    ->toHtml();
        }
        return $this->_addRowButtonHtml[$container];
    }

    protected function _getRemoveRowButtonHtml($selector = 'li', $title = 'Remove')
    {
        if (!$this->_removeRowButtonHtml) {
            $this->_removeRowButtonHtml = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Widget_Button')
                    ->setType('button')
                    ->setClass('delete v-middle ' . $this->_getDisabled())
                    ->setLabel($this->__($title))
                    ->setOnClick("Element.remove($(this).up('" . $selector . "'))")
                    ->setDisabled($this->_getDisabled())
                    ->toHtml();
        }
        return $this->_removeRowButtonHtml;
    }
}
