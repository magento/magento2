<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Fill Sales Data.
 */
class FillEmailAddressStep implements TestStepInterface
{
    /**
     * Sales order create index page.
     *
     * @var OrderCreateIndex
     */
    protected $orderCreateIndex;

    /**
     * Email Address.
     *
     * @var string
     */
    protected $emailAddress;


    /**
     * @constructor
     * @param OrderCreateIndex $orderCreateIndex
     * @param CustomerInjectable $customer
     */
    public function __construct(OrderCreateIndex $orderCreateIndex, CustomerInjectable $customer)
    {
        $this->orderCreateIndex = $orderCreateIndex;
        $this->emailAddress = $customer->getEmail();
    }

    /**
     * Fill Account Data.
     *
     * @return void
     */
    public function run()
    {
        $this->orderCreateIndex->getCreateBlock()->fillEmail($this->emailAddress);
    }
}
