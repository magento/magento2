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
 * Credit Card (Authorize.net) Payment method xml renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Checkout_Payment_Method_Authorizenet extends Mage_Payment_Block_Form_Ccsave
{
    /**
     * Prevent any rendering
     *
     * @return string
     */
    protected function _toHtml()
    {
        return '';
    }

    /**
     * Retrieve payment method model
     *
     * @return Mage_Payment_Model_Method_Abstract
     */
    public function getMethod()
    {
        $method = $this->getData('method');
        if (!$method) {
            $method = Mage::getModel('Mage_Paygate_Model_Authorizenet');
            $this->setData('method', $method);
        }

        return $method;
    }

    /**
     * Add Authorize.net payment method form to payment XML object
     *
     * @param Mage_XmlConnect_Model_Simplexml_Element $paymentItemXmlObj
     * @return Mage_XmlConnect_Model_Simplexml_Element
     */
    public function addPaymentFormToXmlObj(Mage_XmlConnect_Model_Simplexml_Element $paymentItemXmlObj)
    {
        $helper = Mage::helper('Mage_XmlConnect_Helper_Data');
        $method = $this->getMethod();
        if (!$method) {
            return $paymentItemXmlObj;
        }
        $formXmlObj = $paymentItemXmlObj->addChild('form');
        $formXmlObj->addAttribute('name', 'payment_form_' . $method->getCode());
        $formXmlObj->addAttribute('method', 'post');

        $ccTypes = $helper->getArrayAsXmlItemValues($this->getCcAvailableTypes(), $this->getInfoData('cc_type'));

        $ccMonths = $helper->getArrayAsXmlItemValues($this->getCcMonths(), $this->getInfoData('cc_exp_month'));

        $ccYears = $helper->getArrayAsXmlItemValues($this->getCcYears(), $this->getInfoData('cc_exp_year'));

        $verification = '';
        if ($this->hasVerification()) {
            $cvnText = $this->__('Card Verification Number');
            $cvnValidationText = $this->__('Card verification number is wrong');
            $verification =
        '<field name="payment[cc_cid]" type="text" label="' . $cvnText . '" required="true">
            <validators>
                <validator relation="payment[cc_type]" type="credit_card_svn" message="' . $cvnValidationText . '"/>
            </validators>
        </field>';
        }

        $cvnValidationText = $this->__('Credit card number does not match credit card type.');
        $expMonthText = $this->__('Expiration Date - Month');
        $expYearText = $this->__('Expiration Date - Year');
        $xml = <<<EOT
<fieldset>
    <field name="payment[cc_type]" type="select" label="{$this->__('Credit Card Type')}" required="true">
        <values>
            {$ccTypes}
        </values>
    </field>
    <field name="payment[cc_number]" type="text" label="{$this->__('Credit Card Number')}" required="true">
        <validators>
            <validator relation="payment[cc_type]" type="credit_card" message="{$cvnValidationText}"/>
        </validators>
    </field>
    <field name="payment[cc_exp_month]" type="select" label="{$expMonthText}" required="true">
        <values>
            {$ccMonths}
        </values>
    </field>
    <field name="payment[cc_exp_year]" type="select" label="{$expYearText}" required="true">
        <values>
            {$ccYears}
        </values>
    </field>
    {$verification}
</fieldset>
EOT;
        $fieldsetXmlObj = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element', $xml);
        $formXmlObj->appendChild($fieldsetXmlObj);
    }
}
