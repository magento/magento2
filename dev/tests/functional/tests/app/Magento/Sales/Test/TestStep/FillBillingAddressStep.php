<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Customer\Test\Fixture\AddressInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Mtf\TestStep\TestStepInterface;

/**
 * Fill Sales Data.
 */
class FillBillingAddressStep implements TestStepInterface
{
    /**
     * Sales order create index page.
     *
     * @var OrderCreateIndex
     */
    protected $orderCreateIndex;

    /**
     * Address.
     *
     * @var AddressInjectable
     */
    protected $billingAddress;

    /**
     * @constructor
     * @param OrderCreateIndex $orderCreateIndex
     * @param AddressInjectable $billingAddress
     */
    public function __construct(OrderCreateIndex $orderCreateIndex, AddressInjectable $billingAddress)
    {
        $this->orderCreateIndex = $orderCreateIndex;
        $this->billingAddress = $billingAddress;
    }

    /**
     * Fill Sales Data.
     *
     * @return AddressInjectable
     */
    public function run()
    {
        $this->orderCreateIndex->getCreateBlock()->fillAddresses($this->billingAddress);

        return ['billingAddress' => $this->billingAddress];
    }
}
