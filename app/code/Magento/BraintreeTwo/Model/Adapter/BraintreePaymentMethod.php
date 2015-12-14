<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Model\Adapter;

use Braintree\PaymentMethod;
use Braintree\PaymentMethodNonce;
use Braintree\Result\Error;
use Braintree\Result\Successful;

/**
 * BraintreePaymentMethod
 *
 * @codeCoverageIgnore
 */
class BraintreePaymentMethod
{
    /**
     * @param array $attribs
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     */
    public function create(array $attribs)
    {
        return PaymentMethod::create($attribs);
    }

    /**
     * @param string $token
     * @param array $attribs
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     */
    public function update($token, array $attribs)
    {
        return PaymentMethod::update($token, $attribs);
    }

    /**
     * @param string $token
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     */
    public function createNonce($token)
    {
        return PaymentMethodNonce::create($token);
    }
}
