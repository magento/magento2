<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Hostedpro;

use Magento\Customer\Helper\Address;
use Magento\Framework\DataObject;
use Magento\Framework\Locale\Resolver;
use Magento\Payment\Helper\Formatter;
use Magento\Paypal\Model\Hostedpro;
use Magento\Sales\Model\Order;
use Magento\Tax\Helper\Data;

/**
 *  Website Payments Pro Hosted Solution request model to get token.
 */
class Request extends DataObject
{
    use Formatter;

    /**
     * Request's order model
     *
     * @var Order
     */
    protected $order;

    /**
     * Request's Hosted Pro payment method model
     *
     * @var Hostedpro
     */
    protected $paymentMethod;

    /**
     * Name format for button variables
     *
     * @var string
     */
    protected $buttonVarFormat = 'L_BUTTONVAR%d';

    /**
     * Request Parameters which dont have to wrap as button vars
     *
     * @var string[]
     */
    protected $notButtonVars = ['METHOD', 'BUTTONCODE', 'BUTTONTYPE'];

    /**
     * @var Address
     */
    protected $customerAddress = null;

    /**
     * @var Data
     */
    protected $taxData;

    /**
     * @var Resolver
     */
    protected $localeResolver;

    /**
     * @param Resolver $localeResolver
     * @param Address $customerAddress
     * @param Data $taxData
     * @param array $data
     */
    public function __construct(
        Resolver $localeResolver,
        Address $customerAddress,
        Data $taxData,
        array $data = []
    ) {
        $this->customerAddress = $customerAddress;
        $this->localeResolver = $localeResolver;
        $this->taxData = $taxData;
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
                if (in_array($key, $this->notButtonVars)) {
                    $requestData[$key] = $value;
                } else {
                    $varKey = sprintf($this->buttonVarFormat, $i);
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
     * @param Hostedpro $paymentMethod
     * @return $this
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
        $requestData = $this->getPaymentData($paymentMethod);
        $this->addData($requestData);

        return $this;
    }

    /**
     * Append order data to request
     *
     * @param Order $order
     * @return $this
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
        $requestData = $this->getOrderData($order);
        $this->addData($requestData);

        return $this;
    }

    /**
     * Add amount data to request
     *
     * @access public
     * @param Order $order
     * @return $this
     */
    public function setAmount(Order $order)
    {
        $this->addData($this->getAmountData($order));
        return $this;
    }

    /**
     * Calculate amount for order
     *
     * @param Order $order
     * @return array
     * @throws \Exception
     */
    protected function getAmountData(Order $order)
    {
        // if tax is included - need add to request only total amount
        if ($this->taxData->getConfig()->priceIncludesTax()) {
            return $this->getTaxableAmount($order);
        } else {
            return $this->getNonTaxableAmount($order);
        }
    }

    /**
     * Get payment amount data with excluded tax
     *
     * @param Order $order
     * @return array
     */
    private function getNonTaxableAmount(Order $order)
    {
        // PayPal denied transaction with 0 amount
        $subtotal = $order->getBaseSubtotal() ? : $order->getPayment()->getBaseAmountAuthorized();

        return [
            'subtotal' => $this->formatPrice($subtotal),
            'total' => $this->formatPrice($order->getPayment()->getBaseAmountAuthorized()),
            'tax' => $this->formatPrice($order->getBaseTaxAmount()),
            'shipping' => $this->formatPrice($order->getBaseShippingAmount()),
            'discount' => $this->formatPrice(abs((float) $order->getBaseDiscountAmount()))
        ];
    }

    /**
     * Get order amount data with included tax
     *
     * @param Order $order
     * @return array
     */
    private function getTaxableAmount(Order $order)
    {
        $amount = $this->formatPrice($order->getPayment()->getBaseAmountAuthorized());

        return [
            'amount' => $amount,
            'subtotal' => $amount // subtotal always is required
        ];
    }

    /**
     * Get payment request data as array
     *
     * @param Hostedpro $paymentMethod
     * @return array
     */
    protected function getPaymentData(Hostedpro $paymentMethod)
    {
        $paymentAction = $paymentMethod->getConfigData('payment_action');
        $request = [
            'paymentaction' => $paymentAction !== null ? strtolower($paymentAction) : '',
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
     * @param Order $order
     * @return array
     */
    protected function getOrderData(Order $order)
    {
        $request = [
            'invoice' => $order->getIncrementId(),
            'address_override' => 'true',
            'currency_code' => $order->getBaseCurrencyCode(),
            'buyer_email' => $order->getCustomerEmail(),
        ];

        // append to request billing address data
        if ($billingAddress = $order->getBillingAddress()) {
            $request = array_merge($request, $this->getAddress($billingAddress, 'billing'));
        }

        // append to request shipping address data
        if ($shippingAddress = $order->getShippingAddress()) {
            $request = array_merge($request, $this->getAddress($shippingAddress));
        }

        return $request;
    }

    /**
     * Export address data to request
     *
     * @param DataObject $address
     * @param string $type
     * @return array
     */
    protected function getAddress(DataObject $address, $type = '')
    {
        $type = !empty($type) ? $type . '_' : '';
        $request = [
            $type . 'first_name' => $address->getFirstname(),
            $type . 'last_name' => $address->getLastname(),
            $type . 'city' => $address->getCity(),
            $type . 'state' => $this->getRegion($address),
            $type . 'zip' => $address->getPostcode(),
            $type . 'country' => $address->getCountryId(),
        ];

        $streets = $this->getAddressStreets($address);
        $request[$type . 'address1'] = $streets[0];
        $request[$type . 'address2'] = $streets[1];

        return $request;
    }

    /**
     * Export region code from address data
     *
     * @param DataObject $address
     * @return string
     */
    protected function getRegion(DataObject $address)
    {
        // get region code, otherwise - region, otherwise - city
        return $address->getRegionCode() ?: ($address->getRegion() ?: $address->getCity());
    }

    /**
     * Export streets from address data
     *
     * @param DataObject $address
     * @return array
     */
    protected function getAddressStreets(DataObject $address)
    {
        $street1 = '';
        $street2 = '';
        $data = $this->customerAddress->convertStreetLines($address->getStreet(), 2);
        if (!empty($data[0])) {
            $street1 = $data[0];
        }
        if (!empty($data[1])) {
            $street2 = $data[1];
        }
        return [$street1, $street2];
    }
}
