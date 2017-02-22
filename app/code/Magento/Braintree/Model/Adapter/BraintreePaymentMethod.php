<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Adapter;

use \Braintree_PaymentMethod;
use \Braintree_PaymentMethodNonce;
use \Braintree_Result_Error;
use \Braintree_Result_Successful;

/**
 * BraintreePaymentMethod
 *
 * @codeCoverageIgnore
 */
class BraintreePaymentMethod
{
    /**
     * @param array $attribs
     * @return \Braintree_Result_Successful|\Braintree_Result_Error
     */
    public function create(array $attribs)
    {
        return \Braintree_PaymentMethod::create($attribs);
    }

    /**
     * @param string $token
     * @param array $attribs
     * @return \Braintree_Result_Successful|\Braintree_Result_Error
     */
    public function update($token, array $attribs)
    {
        return \Braintree_PaymentMethod::update($token, $attribs);
    }

    /**
     * @param string $token
     * @return \Braintree_Result_Successful|\Braintree_Result_Error
     */
    public function createNonce($token)
    {
        return \Braintree_PaymentMethodNonce::create($token);
    }
}
