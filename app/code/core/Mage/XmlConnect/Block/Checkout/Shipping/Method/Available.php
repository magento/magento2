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
 * One page checkout shipping methods xml renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Checkout_Shipping_Method_Available
    extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
    /**
     * Render shipping methods xml
     *
     * @return string
     */
    protected function _toHtml()
    {
        /** @var $methodsXmlObj Mage_XmlConnect_Model_Simplexml_Element */
        $methodsXmlObj = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element', '<shipping_methods></shipping_methods>');
        $_shippingRateGroups = $this->getShippingRates();
        if ($_shippingRateGroups) {
            $store = $this->getQuote()->getStore();
            $_sole = count($_shippingRateGroups) == 1;
            foreach ($_shippingRateGroups as $code => $_rates) {
                $methodXmlObj = $methodsXmlObj->addChild('method');
                $methodXmlObj->addAttribute('label', $methodsXmlObj->xmlentities($this->getCarrierName($code)));
                $ratesXmlObj = $methodXmlObj->addChild('rates');

                $_sole = $_sole && count($_rates) == 1;
                foreach ($_rates as $_rate) {
                    $rateXmlObj = $ratesXmlObj->addChild('rate');
                    $rateXmlObj->addAttribute('label', $methodsXmlObj->xmlentities($_rate->getMethodTitle()));
                    $rateXmlObj->addAttribute('code', $_rate->getCode());
                    if ($_rate->getErrorMessage()) {
                        $rateXmlObj->addChild('error_message', $methodsXmlObj->xmlentities($_rate->getErrorMessage()));
                    } else {
                        $price = Mage::helper('Mage_Tax_Helper_Data')->getShippingPrice(
                            $_rate->getPrice(),
                            Mage::helper('Mage_Tax_Helper_Data')->displayShippingPriceIncludingTax(),
                            $this->getAddress()
                        );
                        $formattedPrice = $store->convertPrice($price, true, false);
                        $rateXmlObj->addAttribute('price', Mage::helper('Mage_XmlConnect_Helper_Data')->formatPriceForXml(
                            $store->convertPrice($price, false, false)
                        ));
                        $rateXmlObj->addAttribute('formated_price', $formattedPrice);
                    }
                }
            }
        } else {
            Mage::throwException($this->__('Shipping to this address is not possible.'));
        }
        return $methodsXmlObj->asNiceXml();
    }
}
