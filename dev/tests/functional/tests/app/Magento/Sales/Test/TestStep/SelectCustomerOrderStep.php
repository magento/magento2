<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Class SelectCustomerOrderStep
 * Select Customer for Order
 */
class SelectCustomerOrderStep implements TestStepInterface
{
    /**
     * Sales order create index page
     *
     * @var OrderCreateIndex
     */
    protected $orderCreateIndex;

    /**
     * Customer
     *
     * @var Customer
     */
    protected $customer;

    /**
     * @constructor
     * @param OrderCreateIndex $orderCreateIndex
     * @param Customer $customer
     */
    public function __construct(OrderCreateIndex $orderCreateIndex, Customer $customer)
    {
        $this->orderCreateIndex = $orderCreateIndex;
        $this->customer = $customer;
    }

    /**
     * Select Customer for Order
     *
     * @return void
     */
    public function run()
    {
        $this->orderCreateIndex->getCustomerBlock()->selectCustomer($this->customer);
    }
}
