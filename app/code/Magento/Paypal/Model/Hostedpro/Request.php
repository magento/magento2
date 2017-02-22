<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Hostedpro;

use \Magento\Sales\Model\Order;

/**
 *  Website Payments Pro Hosted Solution request model to get token.
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Request extends \Magento\Framework\DataObject
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
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxData;

    /**
     * Locale Resolver
     *
     * @var \Magento\Framework\Locale\Resolver
     */
    protected $localeResolver;

    /**
     * @param \Magento\Framework\Locale\Resolver $localeResolver
     * @param \Magento\Customer\Helper\Address $customerAddress
     * @param \Magento\Tax\Helper\Data $taxData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Locale\Resolver $localeResolver,
        \Magento\Customer\Helper\Address $customerAddress,
        \Magento\Tax\Helper\Data $taxData,
        array $data = []
    ) {
        $this->_customerAddress = $customerAddress;
        $this->localeResolver = $localeResolver;
        $this->_taxData = $taxData;
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
     * Add amount data to request
     *
     * @access public
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     */
    public function setAmount(Order $order)
    {
        $this->addData($this->_getAmountData($order));
        return $this;
    }

    /**
     * Calculate amount for order
     * @param \Magento\Sales\Model\Order $order
     * @return array
     * @throws \Exception
     */
    protected function _getAmountData(Order $order)
    {
        // if tax is included - need add to request only total amount
        if ($this->_taxData->getConfig()->priceIncludesTax()) {
            return $this->getTaxableAmount($order);
        } else {
            return $this->getNonTaxableAmount($order);
        }
    }

    /**
     * Get payment amount data with excluded tax
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    private function getNonTaxableAmount(Order $order)
    {
        return [
            'subtotal' => $this->_formatPrice($order->getBaseSubtotal()),
            'total' => $this->_formatPrice($order->getPayment()->getBaseAmountAuthorized()),
            'tax' => $this->_formatPrice($order->getBaseTaxAmount()),
            'shipping' => $this->_formatPrice($order->getBaseShippingAmount()),
            'discount' => $this->_formatPrice(abs($order->getBaseDiscountAmount()))
        ];
    }

    /**
     * Get order amount data with included tax
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    private function getTaxableAmount(Order $order)
    {
        $amount = $this->_formatPrice($order->getPayment()->getBaseAmountAuthorized());
        return [
            'amount' => $amount,
            'subtotal' => $amount // subtotal always is required
        ];
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
     * @param \Magento\Framework\DataObject $address
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getShippingAddress(\Magento\Framework\DataObject $address)
    {
        $region = $address->getRegionCode() ? $address->getRegionCode() : $address->getRegion();
        $request = [
            'first_name' => $address->getFirstname(),
            'last_name' => $address->getLastname(),
            'city' => $address->getCity(),
            'state' => $region ? $region : $address->getCity(),
            'zip' => $address->getPostcode(),
            'country' => $address->getCountryId(),
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
     * @param \Magento\Framework\DataObject $address
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getBillingAddress(\Magento\Framework\DataObject $address)
    {
        $region = $address->getRegionCode() ? $address->getRegionCode() : $address->getRegion();
        $request = [
            'billing_first_name' => $address->getFirstname(),
            'billing_last_name' => $address->getLastname(),
            'billing_city' => $address->getCity(),
            'billing_state' => $region ? $region : $address->getCity(),
            'billing_zip' => $address->getPostcode(),
            'billing_country' => $address->getCountryId(),
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
