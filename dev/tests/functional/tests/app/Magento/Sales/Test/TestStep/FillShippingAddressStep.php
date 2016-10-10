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
 * Fill Shipping Address.
 */
class FillShippingAddressStep implements TestStepInterface
{
    /**
     * Sales order create index page.
     *
     * @var OrderCreateIndex
     */
    protected $orderCreateIndex;

    /**
     * Shipping Address fixture.
     *
     * @var Address
     */
    protected $shippingAddress;

    /**
     * @param OrderCreateIndex $orderCreateIndex
     * @param Address $shippingAddress [optional]
     */
    public function __construct(
        OrderCreateIndex $orderCreateIndex,
        Address $shippingAddress = null
    ) {
        $this->orderCreateIndex = $orderCreateIndex;
        $this->shippingAddress = $shippingAddress;
    }

    /**
     * Fill Shipping Address.
     *
     * @return void
     */
    public function run()
    {
        if ($this->shippingAddress !== null) {
            $this->orderCreateIndex->getCreateBlock()->fillShippingAddress($this->shippingAddress);
        }
    }
}
