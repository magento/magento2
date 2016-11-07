<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Customer\Test\Fixture\Address;
use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Fill Billing Address.
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
     * Billing Address fixture.
     *
     * @var Address
     */
    protected $billingAddress;

    /**
     * Shipping Address fixture.
     *
     * @var Address
     */
    protected $shippingAddress;

    /**
     * Save Address.
     *
     * @var string
     */
    protected $saveAddress;

    /**
     * Flag to set 'Same as billing address' for shipping address.
     *
     * @var string
     */
    protected $setShippingAddress;

    /**
     * @param OrderCreateIndex $orderCreateIndex
     * @param Address $billingAddress
     * @param Address $shippingAddress [optional]
     * @param string $saveAddress [optional]
     * @param bool $setShippingAddress [optional]
     */
    public function __construct(
        OrderCreateIndex $orderCreateIndex,
        Address $billingAddress,
        Address $shippingAddress = null,
        $saveAddress = 'No',
        $setShippingAddress = true
    ) {
        $this->orderCreateIndex = $orderCreateIndex;
        $this->billingAddress = $billingAddress;
        $this->shippingAddress = $shippingAddress;
        $this->saveAddress = $saveAddress;
        $this->setShippingAddress = $setShippingAddress;
    }

    /**
     * Fill Billing Address.
     *
     * @return array
     */
    public function run()
    {
        if ($this->shippingAddress !== null) {
            $this->setShippingAddress = null;
        }
        $this->orderCreateIndex->getCreateBlock()
            ->fillBillingAddress($this->billingAddress, $this->saveAddress, $this->setShippingAddress);

        return ['billingAddress' => $this->billingAddress];
    }
}
