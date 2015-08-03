<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Hostedpro;

/**
 *  Website Payments Pro Hosted Solution request model to get token.
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Request extends \Magento\Framework\Object
{
    /**
     * Request's order model
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * Request's Hosted Pro payment method model
     *
     * @var \Magento\Paypal\Model\Hostedpro
     */
    protected $_paymentMethod;

    /**
     * Name format for button variables
     *
     * @var string
     */
    protected $_buttonVarFormat = 'L_BUTTONVAR%d';

    /**
     * Request Parameters which dont have to wrap as button vars
     *
     * @var string[]
     */
    protected $_notButtonVars = ['METHOD', 'BUTTONCODE', 'BUTTONTYPE'];

    /**
     * Customer address
     *
     * @var \Magento\Customer\Helper\Address
     */
    protected $_customerAddress = null;

    /**
     * Locale Resolver
     *
     * @var \Magento\Framework\Locale\Resolver
     */
    protected $localeResolver;

    /**
     * @param \Magento\Framework\Locale\Resolver $localeResolver
     * @param \Magento\Customer\Helper\Address $customerAddress
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Locale\Resolver $localeResolver,
        \Magento\Customer\Helper\Address $customerAddress,
        array $data = []
    ) {
        $this->_customerAddress = $customerAddress;
        $this->localeResolver = $localeResolver;
        parent::__construct($data);
    }

    /**
     * Build and return request array from object data
     *
     * @return array
     */
    public function getRequestData()
    {
        $requestData = [];
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
     * @param \Magento\Paypal\Model\Hostedpro $paymentMethod
     * @return $this
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
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->_order = $order;
        $requestData = $this->_getOrderData($order);
        $this->addData($requestData);

        return $this;
    }

    /**
     * Get payment request data as array
     *
     * @param \Magento\Paypal\Model\Hostedpro $paymentMethod
     * @return array
     */
    protected function _getPaymentData(\Magento\Paypal\Model\Hostedpro $paymentMethod)
    {
        $request = [
            'paymentaction' => strtolower($paymentMethod->getConfigData('payment_action')),
            'notify_url' => $paymentMethod->getNotifyUrl(),
            'cancel_return' => $paymentMethod->getCancelUrl(),
            'return' => $paymentMethod->getReturnUrl(),
            'lc' => \Locale::getRegion($this->localeResolver->getLocale()),
            'template' => 'mobile-iframe',
            'showBillingAddress' => 'false',
            'showShippingAddress' => 'true',
            'showBillingEmail' => 'false',
            'showBillingPhone' => 'false',
            'showCustomerName' => 'false',
            'showCardInfo' => 'true',
            'showHostedThankyouPage' => 'false',
        ];

        return $request;
    }

    /**
     * Get order request data as array
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    protected function _getOrderData(\Magento\Sales\Model\Order $order)
    {
        $request = [
            'subtotal' => $this->_formatPrice(
                $this->_formatPrice(
                    $order->getPayment()->getBaseAmountAuthorized()
                ) - $this->_formatPrice(
                    $order->getBaseTaxAmount()
                ) - $this->_formatPrice(
                    $order->getBaseShippingAmount()
                )
            ),
            'tax' => $this->_formatPrice($order->getBaseTaxAmount()),
            'shipping' => $this->_formatPrice($order->getBaseShippingAmount()),
            'invoice' => $order->getIncrementId(),
            'address_override' => 'true',
            'currency_code' => $order->getBaseCurrencyCode(),
            'buyer_email' => $order->getCustomerEmail(),
        ];

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
     * @param \Magento\Framework\Object $address
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getShippingAddress(\Magento\Framework\Object $address)
    {
        $region = $address->getRegionCode() ? $address->getRegionCode() : $address->getRegion();
        $request = [
            'first_name' => $address->getFirstname(),
            'last_name' => $address->getLastname(),
            'city' => $address->getCity(),
            'state' => $region ? $region : $address->getCity(),
            'zip' => $address->getPostcode(),
            'country' => $address->getCountry(),
        ];

        // convert streets to tow lines format
        $street = $this->_customerAddress->convertStreetLines($address->getStreet(), 2);

        $request['address1'] = isset($street[0]) ? $street[0] : '';
        $request['address2'] = isset($street[1]) ? $street[1] : '';

        return $request;
    }

    /**
     * Get billing address request data
     *
     * @param \Magento\Framework\Object $address
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getBillingAddress(\Magento\Framework\Object $address)
    {
        $region = $address->getRegionCode() ? $address->getRegionCode() : $address->getRegion();
        $request = [
            'billing_first_name' => $address->getFirstname(),
            'billing_last_name' => $address->getLastname(),
            'billing_city' => $address->getCity(),
            'billing_state' => $region ? $region : $address->getCity(),
            'billing_zip' => $address->getPostcode(),
            'billing_country' => $address->getCountry(),
        ];

        // convert streets to tow lines format
        $street = $this->_customerAddress->convertStreetLines($address->getStreet(), 2);

        $request['billing_address1'] = isset($street[0]) ? $street[0] : '';
        $request['billing_address2'] = isset($street[1]) ? $street[1] : '';

        return $request;
    }

    /**
     * Format price string
     *
     * @param mixed $string
     * @return string
     */
    protected function _formatPrice($string)
    {
        return sprintf('%.2F', $string);
    }
}
