<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Flag for set same as billing shipping address.
     *
     * @var string
     */
    protected $setShippingAddress;

    /**
     * @constructor
     * @param OrderCreateIndex $orderCreateIndex
     * @param Address $billingAddress
     * @param string $saveAddress
     * @param bool $setShippingAddress [optional]
     */
    public function __construct(
        OrderCreateIndex $orderCreateIndex,
        Address $billingAddress,
        $saveAddress = 'No',
        $setShippingAddress = true
    ) {
        $this->orderCreateIndex = $orderCreateIndex;
        $this->billingAddress = $billingAddress;
        $this->saveAddress = $saveAddress;
        $this->setShippingAddress = $setShippingAddress;
    }

    /**
     * Fill Sales Data.
     *
     * @return Address
     */
    public function run()
    {
        $this->orderCreateIndex->getCreateBlock()
            ->fillAddresses($this->billingAddress, $this->saveAddress, $this->setShippingAddress);

        return ['billingAddress' => $this->billingAddress];
    }
}
