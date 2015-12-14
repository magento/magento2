<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Model\Adapter;

use Braintree\Customer;
use Braintree\Result\Successful;

/**
 * BraintreeCustomer
 *
 * @codeCoverageIgnore
 */
class BraintreeCustomer
{
    /**
     * @param string $customerId
     * @return \Braintree\Customer
     */
    public function find($customerId)
    {
        return Customer::find($customerId);
    }

    /**
     * @param string $customerId
     * @return \Braintree\Result\Successful
     */
    public function delete($customerId)
    {
        return Customer::delete($customerId);
    }

    /**
     * @param array $customerRequest
     * @return \Braintree\Customer
     */
    public function create(array $customerRequest)
    {
        return Customer::create($customerRequest);
    }
}
