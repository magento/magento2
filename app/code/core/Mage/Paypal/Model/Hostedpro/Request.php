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
 * @package     Mage_Paypal
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *  Website Payments Pro Hosted Solution request model to get token.
 *
 * @category    Mage
 * @package     Mage_Paypal
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Paypal_Model_Hostedpro_Request extends Varien_Object
{
    /**
     * Request's order model
     *
     * @var Mage_Sales_Model_Order
     */
    protected $_order;

    /**
     * Request's Hosted Pro payment method model
     *
     * @var Mage_Paypal_Model_Hostedpro
     */
    protected $_paymentMethod;

    /**
     * Name formate for button variables
     *
     * @var string
     */
    protected $_buttonVarFormat = 'L_BUTTONVAR%d';

    /**
     * Request Parameters which dont have to wrap as button vars
     *
     * @var array
     */
    protected $_notButtonVars = array (
        'METHOD', 'BUTTONCODE', 'BUTTONTYPE');

    /**
     * Build and return request array from object data
     *
     * @return array
     */
    public function getRequestData()
    {
        $requestData = array();
        if (!empty($this->_data)) {
            // insert params to request as additional button variables,
            // except special params from _notButtonVars list
            $i = 0;
            foreach ($this->_data as $key => $value) {
                if (in_array($key, $this->_notButtonVars)) {
                    $requestData[$key] = $value;
                } else {
                    $varKey = sprintf($this->_buttonVarFormat, $i);
                    $requestData[$varKey] = $key . '=' . $value;
                    $i++;
                }
            }
        }

        return $requestData;
    }

    /**
     * Append payment data to request
     *
     * @param Mage_Paypal_Model_Hostedpro $paymentMethod
     * @return Mage_Paypal_Model_Hostedpro_Request
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->_paymentMethod = $paymentMethod;
        $requestData = $this->_getPaymentData($paymentMethod);
        $this->addData($requestData);

        return $this;
    }

    /**
     * Append order data to request
     *
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Paypal_Model_Hostedpro_Request
     */
    public function setOrder($order)
    {
        $this->_order = $order;
        $requestData = $this->_getOrderData($order);
        $this->addData($requestData);

        return $this;
    }

    /**
     * Get peymet request data as array
     *
     * @param Mage_Paypal_Model_Hostedpro $paymentMethod
     * @return array
     */
    protected function _getPaymentData(Mage_Paypal_Model_Hostedpro $paymentMethod)
    {
        $request = array(
            'paymentaction' => strtolower($paymentMethod->getConfigData('payment_action')),
            'notify_url'    => $paymentMethod->getNotifyUrl(),
            'cancel_return' => $paymentMethod->getCancelUrl(),
            'return'        => $paymentMethod->getReturnUrl(),
            'lc'            => $paymentMethod->getMerchantCountry(),

            'template'              => 'templateD',
            'showBillingAddress'    => 'false',
            'showShippingAddress'   => 'false',
            'showBillingEmail'      => 'false',
            'showBillingPhone'      => 'false',
            'showCustomerName'      => 'false',
            'showCardInfo'          => 'true',
            'showHostedThankyouPage'=> 'false'
        );

        return $request;
    }

    /**
     * Get order request data as array
     *
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    protected function _getOrderData(Mage_Sales_Model_Order $order)
    {
        $request = array(
            'subtotal'      => $this->_formatPrice(
                $this->_formatPrice($order->getPayment()->getBaseAmountAuthorized()) -
                $this->_formatPrice($order->getBaseTaxAmount()) -
                $this->_formatPrice($order->getBaseShippingAmount())
            ),
            'tax'           => $this->_formatPrice($order->getBaseTaxAmount()),
            'shipping'      => $this->_formatPrice($order->getBaseShippingAmount()),
            'invoice'       => $order->getIncrementId(),
            'address_override' => 'false',
            'currency_code'    => $order->getBaseCurrencyCode(),
            'buyer_email'      => $order->getCustomerEmail()
        );

        // append to request billing address data
        if ($billingAddress = $order->getBillingAddress()) {
            $request = array_merge($request, $this->_getBillingAddress($billingAddress));
        }

        // append to request shipping address data
        if ($shippingAddress = $order->getShippingAddress()) {
            $request = array_merge($request, $this->_getShippingAddress($shippingAddress));
        }

        return $request;
    }

    /**
     * Get shipping address request data
     *
     * @param Varien_Object $address
     * @return array
     */
    protected function _getShippingAddress(Varien_Object $address)
    {
        $request = array(
            'first_name'=> $address->getFirstname(),
            'last_name' => $address->getLastname(),
            'city'      => $address->getCity(),
            'state'     => $address->getRegion(),
            'zip'       => $address->getPostcode(),
            'country'   => $address->getCountry(),
        );

        // convert streets to tow lines format
        $street = Mage::helper('Mage_Customer_Helper_Address')
            ->convertStreetLines($address->getStreet(), 2);

        $request['address1'] = isset($street[0]) ? $street[0]: '';
        $request['address2'] = isset($street[1]) ? $street[1]: '';

        return $request;
    }

    /**
     * Get billing address request data
     *
     * @param Varien_Object $address
     * @return array
     */
    protected function _getBillingAddress(Varien_Object $address)
    {
        $request = array(
            'billing_first_name'=> $address->getFirstname(),
            'billing_last_name' => $address->getLastname(),
            'billing_city'      => $address->getCity(),
            'billing_state'     => $address->getRegion(),
            'billing_zip'       => $address->getPostcode(),
            'billing_country'   => $address->getCountry(),
        );

        // convert streets to tow lines format
        $street = Mage::helper('Mage_Customer_Helper_Address')
            ->convertStreetLines($address->getStreet(), 2);

        $request['billing_address1'] = isset($street[0]) ? $street[0]: '';
        $request['billing_address2'] = isset($street[1]) ? $street[1]: '';

        return $request;
    }

    /**
     * Format price string
     *
     * @param mixed $string
     * @return mixed
     */
    protected function _formatPrice($string)
    {
        return sprintf('%.2F', $string);
    }
}
