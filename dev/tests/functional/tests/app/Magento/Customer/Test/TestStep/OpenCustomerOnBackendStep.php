<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestStep;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Class OpenCustomerOnBackendStep
 * Open customer account
 */
class OpenCustomerOnBackendStep implements TestStepInterface
{
    /**
     * Customer fixture
     *
     * @var Customer
     */
    protected $customer;

    /**
     * Customer index page
     *
     * @var Customer
     */
    protected $customerIndex;

    /**
     * @constructor
     * @param Customer $customer
     * @param CustomerIndex $customerIndex
     */
    public function __construct(Customer $customer, CustomerIndex $customerIndex)
    {
        $this->customer = $customer;
        $this->customerIndex = $customerIndex;
    }

    /**
     * Open customer account
     *
     * @return void
     */
    public function run()
    {
        $this->customerIndex->open();
        $this->customerIndex->getCustomerGridBlock()->searchAndOpen(['email' => $this->customer->getEmail()]);
    }
}
