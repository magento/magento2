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
 * Credit Card (Payflow Pro) Payment method xml renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Checkout_Payment_Method_Paypal_Payflow extends Mage_Payment_Block_Form_Ccsave
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
            $method = Mage::getModel('Mage_Paypal_Model_Payflowpro');
            $this->setData('method', $method);
        }

        return $method;
    }

    /**
     * Add Payflow Pro payment method form to payment XML object
     *
     * @param Mage_XmlConnect_Model_Simplexml_Element $paymentItemXmlObj
     * @return Mage_XmlConnect_Model_Simplexml_Element
     */
    public function addPaymentFormToXmlObj(Mage_XmlConnect_Model_Simplexml_Element $paymentItemXmlObj)
    {
        $method = $this->getMethod();
        if (!$method) {
            return $paymentItemXmlObj;
        }
        $formXmlObj = $paymentItemXmlObj->addChild('form');
        $formXmlObj->addAttribute('name', 'payment_form_' . $method->getCode());
        $formXmlObj->addAttribute('method', 'post');

        $_ccType = $this->getInfoData('cc_type');
        $ccTypes = '';

        foreach ($this->getCcAvailableTypes() as $_typeCode => $_typeName) {
            if (!$_typeCode) {
                continue;
            }
            $ccTypes .= '
            <item' . ($_typeCode == $_ccType ? ' selected="1"' : '') . '>
                <label>' . $_typeName . '</label>
                <value>' . $_typeCode . '</value>
            </item>';
        }

        $ccMonthes = '';

        $_ccExpMonth = $this->getInfoData('cc_exp_month');
        foreach ($this->getCcMonths() as $k => $v) {
            if (!$k) {
                continue;
            }
            $ccMonthes .= '
            <item' . ($k == $_ccExpMonth ? ' selected="1"' : '') . '>
                <label>' . $v . '</label>
                <value>' . ($k ? $k : '') . '</value>
            </item>';
        }

        $ccYears = '';

        $_ccExpYear = $this->getInfoData('cc_exp_year');
        foreach ($this->getCcYears() as $k => $v) {
            if (!$k) {
                continue;
            }
            $ccYears .= '
            <item' . ($k == $_ccExpYear ? ' selected="1"' : '') . '>
                <label>' . $v . '</label>
                <value>' . ($k ? $k : '') . '</value>
            </item>';
        }

        $verification = '';
        if ($this->hasVerification()) {
            $verification = <<<EOT
<field name="payment[cc_cid]" type="text" label="{$this->__('Card Verification Number')}" required="true">
    <validators>
        <validator relation="payment[cc_type]" type="credit_card_svn" message="{$this->__('Card verification number is wrong')}'"/>
    </validators>
</field>
EOT;
        }

        $xml = <<<EOT
<fieldset>
    <field name="payment[cc_type]" type="select" label="{$this->__('Credit Card Type')}" required="true">
        <values>
            $ccTypes
        </values>
    </field>
    <field name="payment[cc_number]" type="text" label="{$this->__('Credit Card Number')}" required="true">
        <validators>
            <validator relation="payment[cc_type]" type="credit_card" message="{$this->__('Credit card number does not match credit card type.')}"/>
        </validators>
    </field>
    <field name="payment[cc_exp_month]" type="select" label="{$this->__('Expiration Date - Month')}" required="true">
        <values>
            $ccMonthes
        </values>
    </field>
    <field name="payment[cc_exp_year]" type="select" label="{$this->helper('Mage_XmlConnect_Helper_Data')->__('Expiration Date - Year')}" required="true">
        <values>
            $ccYears
        </values>
    </field>
    $verification
</fieldset>
EOT;
        $fieldsetXmlObj = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element', $xml);
        $formXmlObj->appendChild($fieldsetXmlObj);
    }
}
