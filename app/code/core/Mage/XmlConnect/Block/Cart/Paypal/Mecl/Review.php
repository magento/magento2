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
 * PayPal MECL Shopping cart review xml renderer
 *
 * @category    Mage
 * @package     Mage_Xmlconnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Cart_Paypal_Mecl_Review extends Mage_Paypal_Block_Express_Review
{
    /**
     * Render PayPal MECL details xml
     *
     * @return string xml
     */
    protected function _toHtml()
    {
        /** @var $reviewXmlObj Mage_XmlConnect_Model_Simplexml_Element */
        $reviewXmlObj = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element',
            array('data' => '<mecl_cart_details></mecl_cart_details>'));

        if ($this->getPaypalMessages()) {
            $reviewXmlObj->addChild('paypal_message', implode(PHP_EOL, $this->getPaypalMessages()));
        }

        if ($this->getShippingAddress()) {
            $reviewXmlObj->addCustomChild(
                'shipping_address',
                Mage::helper('Mage_XmlConnect_Helper_Data')->trimLineBreaks($this->getShippingAddress()->format('text')),
                array('label' => $this->__('Shipping Address'))
            );
        }

        if ($this->_quote->isVirtual()) {
            $reviewXmlObj->addCustomChild('shipping_method', null, array(
                'label' => $this->__('No shipping method required.')
            ));
        } elseif ($this->getCanEditShippingMethod() || !$this->getCurrentShippingRate()) {
            if ($groups = $this->getShippingRateGroups()) {
                $currentRate = $this->getCurrentShippingRate();
                foreach ($groups as $code => $rates) {
                    foreach ($rates as $rate) {
                        if ($currentRate === $rate) {
                            $reviewXmlObj->addCustomChild('shipping_method', null, array(
                                'rate' => strip_tags($this->renderShippingRateOption($rate)),
                                'label' => $this->getCarrierName($code)
                            ));
                            break(2);
                        }
                    }
                }
            }
        }
        $reviewXmlObj->addCustomChild('payment_method', $this->escapeHtml($this->getPaymentMethodTitle()), array(
            'label' => $this->__('Payment Method')
        ));

        $reviewXmlObj->addCustomChild(
            'billing_address',
            Mage::helper('Mage_XmlConnect_Helper_Data')->trimLineBreaks($this->getBillingAddress()->format('text')),
            array(
                'label'         => $this->__('Billing Address'),
                'payer_email'   => $this->__('Payer Email: %s', $this->getBillingAddress()->getEmail())
        ));

        $this->getChildBlock('details')->addDetailsToXmlObj($reviewXmlObj);

        return $reviewXmlObj->asNiceXml();
    }
}
