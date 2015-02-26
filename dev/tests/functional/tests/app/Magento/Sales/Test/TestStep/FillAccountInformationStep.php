<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Fill order account information.
 */
class FillAccountInformationStep implements TestStepInterface
{
    /**
     * Sales order create index page.
     *
     * @var OrderCreateIndex
     */
    protected $orderCreateIndex;

    /**
     * Customer fixtrure.
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
     * Fill Order Account Data.
     *
     * @return void
     */
    public function run()
    {
        $this->orderCreateIndex->getCreateBlock()->getAccountBlock()->fill($this->customer);
    }
}
