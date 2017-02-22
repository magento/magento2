<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model;

use Magento\Sales\Model\Order\Payment;
use Magento\Quote\Model\Quote;
use Magento\Braintree\Model\PaymentMethod\PayPal as BraintreePayPal;
use Magento\Customer\Model\AccountManagement;
use Magento\Quote\Model\Quote\Address;

class Checkout extends \Magento\Paypal\Model\Express\Checkout
{
    /**
     * Payment method type
     *
     * @var string
     */
    protected $_methodType = BraintreePayPal::METHOD_CODE;

    /**
     * Update quote when returned from PayPal
     * export shipping address in case address absence
     *
     * @param string $paymentMethodNonce
     * @param array $details
     * @return void
     */
    public function initializeQuoteForReview($paymentMethodNonce, array $details)
    {
        $quote = $this->_quote;

        $this->populateQuoteAddress($quote, $details);

        // import payment info
        $payment = $quote->getPayment();
        $payment->setMethod($this->_methodType);
        $payment->setAdditionalInformation('payment_method_nonce', $paymentMethodNonce);
        $payment->setAdditionalInformation('payerEmail', $details['email']);
        $payment->setAdditionalInformation('payerFirstName', $details['firstName']);
        $payment->setAdditionalInformation('payerLastName', $details['lastName']);

        $this->_quote->setMayEditShippingAddress(
            true
        );
        $this->_quote->setMayEditShippingMethod(
            true
        );

        $this->ignoreAddressValidation();

        $quote->collectTotals();
        $this->quoteRepository->save($quote);
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param array $details
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function populateQuoteAddress($quote, $details)
    {
        // import shipping address
        $exportedShippingAddress = isset($details['shippingAddress']) ? $details['shippingAddress'] : null;
        if (!$quote->getIsVirtual()) {
            $shippingAddress = $quote->getShippingAddress();
            if ($exportedShippingAddress) {
                $this->importAddressData($shippingAddress, $exportedShippingAddress);
            }
            // PayPal doesn't provide detailed shipping info: prefix, suffix
            $shippingAddress->setLastname($details['lastName']);
            $shippingAddress->setFirstname($details['firstName']);
            $shippingAddress->setEmail($details['email']);
            $shippingAddress->setCollectShippingRates(true);
        }

        $exportedBillingAddress = isset($details['billingAddress']) ? $details['billingAddress'] : null;
        $billingAddress = $quote->getBillingAddress();
        if ($exportedBillingAddress) {
            $this->importBillingAddressData($billingAddress, $exportedBillingAddress);
            $billingAddress->setFirstname($details['firstName']);
            $billingAddress->setLastname($details['lastName']);
            $billingAddress->setEmail($details['email']);
        } elseif ($exportedShippingAddress) {
            $this->importAddressData($billingAddress, $exportedShippingAddress);
            $billingAddress->setFirstname($details['firstName']);
            $billingAddress->setLastname($details['lastName']);
            $billingAddress->setEmail($details['email']);
        }

        return $this;
    }

    /**
     * Make sure addresses will be saved without validation errors
     *
     * @return void
     */
    private function ignoreAddressValidation()
    {
        $this->_quote->getBillingAddress()->setShouldIgnoreValidation(true);
        if (!$this->_quote->getIsVirtual()) {
            $this->_quote->getShippingAddress()->setShouldIgnoreValidation(true);
            if (!$this->_quote->getBillingAddress()->getEmail()) {
                $this->_quote->getBillingAddress()->setSameAsBilling(1);
            }
        }
    }

    /**
     * Sets address data from exported address
     *
     * @param Address $address
     * @param array $exportedAddress
     * @return $this
     */
    protected function importAddressData($address, $exportedAddress)
    {
        $extendedAddress = isset($exportedAddress['extendedAddress']) ? $exportedAddress['extendedAddress'] : null;
        $address->setStreet([$exportedAddress['streetAddress'], $extendedAddress]);
        $address->setCity($exportedAddress['locality']);
        $address->setRegionCode($exportedAddress['region']);
        $address->setCountryId($exportedAddress['countryCodeAlpha2']);
        $address->setPostcode($exportedAddress['postalCode']);
        return $this;
    }

    /**
     * The billing address returned from Braintree may have different format than the shipping address
     *
     * @param Address $address
     * @param array $exportedAddress
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function importBillingAddressData($address, $exportedAddress)
    {
        $line1 = isset($exportedAddress['streetAddress']) ?
            $exportedAddress['streetAddress'] :
            $exportedAddress['line1'];
        $line2 = isset($exportedAddress['extendedAddress']) ?
            $exportedAddress['extendedAddress'] :
            $exportedAddress['line2'];
        $city = isset($exportedAddress['locality']) ?
            $exportedAddress['locality'] :
            $exportedAddress['city'];
        $regionCode = isset($exportedAddress['region']) ?
            $exportedAddress['region'] :
            $exportedAddress['state'];
        $countryCode = isset($exportedAddress['countryCodeAlpha2']) ?
            $exportedAddress['countryCodeAlpha2'] :
            $exportedAddress['countryCode'];

        $address->setStreet([$line1, $line2]);
        $address->setCity($city);
        $address->setRegionCode($regionCode);
        $address->setCountryId($countryCode);
        $address->setPostcode($exportedAddress['postalCode']);
        return $this;
    }
}
