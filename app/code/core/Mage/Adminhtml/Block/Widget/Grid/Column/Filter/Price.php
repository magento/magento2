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
 * Range grid column filter
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Price extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Abstract
{
    protected $_currencyList = null;
    protected $_currencyModel = null;

    public function getHtml()
    {
        $html  = '<div class="range">';
        $html .= '<div class="range-line"><span class="label">' . Mage::helper('Mage_Adminhtml_Helper_Data')->__('From').':</span> <input type="text" name="'.$this->_getHtmlName().'[from]" id="'.$this->_getHtmlId().'_from" value="'.$this->getEscapedValue('from').'" class="input-text no-changes"/></div>';
        $html .= '<div class="range-line"><span class="label">' . Mage::helper('Mage_Adminhtml_Helper_Data')->__('To').' : </span><input type="text" name="'.$this->_getHtmlName().'[to]" id="'.$this->_getHtmlId().'_to" value="'.$this->getEscapedValue('to').'" class="input-text no-changes"/></div>';
        if ($this->getDisplayCurrencySelect())
            $html .= '<div class="range-line"><span class="label">' . Mage::helper('Mage_Adminhtml_Helper_Data')->__('In').' : </span>' . $this->_getCurrencySelectHtml() . '</div>';
        $html .= '</div>';

        return $html;
    }

    public function getDisplayCurrencySelect()
    {
        if (!is_null($this->getColumn()->getData('display_currency_select'))) {
            return $this->getColumn()->getData('display_currency_select');
        } else {
            return true;
        }
    }

    public function getCurrencyAffect()
    {
        if (!is_null($this->getColumn()->getData('currency_affect'))) {
            return $this->getColumn()->getData('currency_affect');
        } else {
            return true;
        }
    }

    protected function _getCurrencyModel()
    {
        if (is_null($this->_currencyModel))
            $this->_currencyModel = Mage::getModel('Mage_Directory_Model_Currency');

        return $this->_currencyModel;
    }

    protected function _getCurrencySelectHtml()
    {

        $value = $this->getEscapedValue('currency');
        if (!$value)
            $value = $this->getColumn()->getCurrencyCode();

        $html  = '';
        $html .= '<select name="'.$this->_getHtmlName().'[currency]" id="'.$this->_getHtmlId().'_currency">';
        foreach ($this->_getCurrencyList() as $currency) {
            $html .= '<option value="' . $currency . '" '.($currency == $value ? 'selected="selected"' : '').'>' . $currency . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

    protected function _getCurrencyList()
    {
        if (is_null($this->_currencyList)) {
            $this->_currencyList = $this->_getCurrencyModel()->getConfigAllowCurrencies();
        }
        return $this->_currencyList;
    }

    public function getValue($index=null)
    {
        if ($index) {
            return $this->getData('value', $index);
        }
        $value = $this->getData('value');
        if ((isset($value['from']) && strlen($value['from']) > 0) || (isset($value['to']) && strlen($value['to']) > 0)) {
            return $value;
        }
        return null;
    }


    public function getCondition()
    {
        $value = $this->getValue();

        if (isset($value['currency']) && $this->getCurrencyAffect()) {
            $displayCurrency = $value['currency'];
        } else {
            $displayCurrency = $this->getColumn()->getCurrencyCode();
        }
        $rate = $this->_getRate($displayCurrency, $this->getColumn()->getCurrencyCode());

        if (isset($value['from']))
            $value['from'] *= $rate;

        if (isset($value['to']))
            $value['to'] *= $rate;

        $this->prepareRates($displayCurrency);
        return $value;
    }

    protected function _getRate($from, $to)
    {
        return Mage::getModel('Mage_Directory_Model_Currency')->load($from)->getAnyRate($to);
    }

    public function prepareRates($displayCurrency)
    {
        $storeCurrency = $this->getColumn()->getCurrencyCode();

        $rate = $this->_getRate($storeCurrency, $displayCurrency);
        if ($rate) {
            $this->getColumn()->setRate($rate);
            $this->getColumn()->setCurrencyCode($displayCurrency);
        }
    }
}
