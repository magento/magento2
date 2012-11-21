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
 * PayPal MEP Shopping cart totals xml renderer
 *
 * @category    Mage
 * @package     Mage_Xmlconnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Cart_Paypal_Mep_Totals extends Mage_Checkout_Block_Cart_Totals
{
    /**
     * Render cart totals xml
     *
     * @return string
     */
    protected function _toHtml()
    {
        /** @var $paypalCart Mage_Paypal_Model_Cart */
        $paypalCart = Mage::getModel('Mage_Paypal_Model_Cart', array('params' => array($this->getQuote())));
        /** @var $totalsXmlObj Mage_XmlConnect_Model_Simplexml_Element */
        $totalsXmlObj = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element',
            array('data' => '<cart_totals></cart_totals>'));
        foreach ($paypalCart->getTotals(true) as $code => $amount) {
            $currencyAmount = $this->helper('Mage_Core_Helper_Data')->currency($amount, false, false);
            $totalsXmlObj->addChild($code, sprintf('%01.2F', $currencyAmount));
        }

        $paypalTotals = $totalsXmlObj->addChild('paypal_totals');
        foreach ($this->getQuote()->getTotals() as $total) {
            $code  = $total->getCode();
            if ($code == 'giftcardaccount' || $code == 'giftwrapping') {
                continue;
            }
            $renderer = $this->_getTotalRenderer($code)->setTotal($total);
            switch ($code) {
                case 'subtotal':
                    $subtotal = intval($total->getValueExclTax()) ? $total->getValueExclTax() : $total->getValue();
                    $paypalTotals->addAttribute(
                        $code,
                        Mage::helper('Mage_XmlConnect_Helper_Data')->formatPriceForXml($subtotal)
                    );
                    break;
                case 'tax':
                    $paypalTotals->addAttribute(
                        $code,
                        Mage::helper('Mage_XmlConnect_Helper_Data')->formatPriceForXml($total->getValue())
                    );
                    break;
                case 'shipping':
                    $paypalTotals->addAttribute(
                        $code,
                        Mage::helper('Mage_XmlConnect_Helper_Data')->formatPriceForXml($renderer->getShippingExcludeTax())
                    );
                    break;
                default:
                    break;
            }
        }

        return $totalsXmlObj->asNiceXml();
    }
}
