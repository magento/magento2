<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestStep;

use Magento\Customer\Test\Fixture\CustomerInjectable;
use Mtf\TestStep\TestStepInterface;

/**
 * Class CreateCustomerStep
 * Create customer using handler
 */
class CreateCustomerStep implements TestStepInterface
{
    /**
     * Customer fixture
     *
     * @var CustomerInjectable
     */
    protected $customer;

    /**
     * Flag for customer creation by handler
     *
     * @var bool
     */
    protected $persistCustomer = true;

    /**
     * @constructor
     * @param CustomerInjectable $customer
     * @param string $checkoutMethod
     */
    public function __construct(CustomerInjectable $customer, $checkoutMethod = '')
    {
        $this->customer = $customer;
        if ($checkoutMethod === 'register' || $checkoutMethod === 'guest') {
            $this->persistCustomer = false;
        }
    }

    /**
     * Create customer
     *
     * @return array
     */
    public function run()
    {
        if ($this->persistCustomer) {
            $this->customer->persist();
        }

        return ['customer' => $this->customer];
    }
}
