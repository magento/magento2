<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Adapter;

use \Braintree_Customer;
use \Braintree_Result_Successful;

/**
 * BraintreeCustomer
 *
 * @codeCoverageIgnore
 */
class BraintreeCustomer
{
    /**
     * @param string $customerId
     * @return \Braintree_Customer
     */
    public function find($customerId)
    {
        return \Braintree_Customer::find($customerId);
    }

    /**
     * @param string $customerId
     * @return \Braintree_Result_Successful
     */
    public function delete($customerId)
    {
        return \Braintree_Customer::delete($customerId);
    }

    /**
     * @param array $customerRequest
     * @return \Braintree_Customer
     */
    public function create(array $customerRequest)
    {
        return \Braintree_Customer::create($customerRequest);
    }
}
