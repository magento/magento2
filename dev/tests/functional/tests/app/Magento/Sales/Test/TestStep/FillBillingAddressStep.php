<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Customer\Test\Fixture\Address;
use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Magento\Mtf\TestStep\TestStepInterface;

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
     * @var Address
     */
    protected $billingAddress;

    /**
     * Save Address.
     *
     * @var string
     */
    protected $saveAddress;

    /**
     * @constructor
     * @param OrderCreateIndex $orderCreateIndex
     * @param Address $billingAddress
     * @param string $saveAddress
     */
    public function __construct(OrderCreateIndex $orderCreateIndex, Address $billingAddress, $saveAddress = 'No')
    {
        $this->orderCreateIndex = $orderCreateIndex;
        $this->billingAddress = $billingAddress;
        $this->saveAddress = $saveAddress;
    }

    /**
     * Fill Sales Data.
     *
     * @return Address
     */
    public function run()
    {
        $this->orderCreateIndex->getCreateBlock()->fillAddresses($this->billingAddress, $this->saveAddress);

        return ['billingAddress' => $this->billingAddress];
    }
}
