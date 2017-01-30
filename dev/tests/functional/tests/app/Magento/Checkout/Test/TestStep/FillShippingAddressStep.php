<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Customer\Test\Fixture\Address;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Fill shipping address step.
 */
class FillShippingAddressStep implements TestStepInterface
{
    /**
     * Onepage checkout page.
     *
     * @var CheckoutOnepage
     */
    protected $checkoutOnepage;

    /**
     * Address fixture.
     *
     * @var Address
     */
    protected $shippingAddress;

    /**
     * @constructor
     * @param CheckoutOnepage $checkoutOnepage
     * @param Address $shippingAddress
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        Address $shippingAddress = null
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->shippingAddress = $shippingAddress;
    }

    /**
     * Fill shipping address.
     *
     * @return void
     */
    public function run()
    {
        if ($this->shippingAddress) {
            $this->checkoutOnepage->getShippingBlock()->fill($this->shippingAddress);
        }
    }
}
