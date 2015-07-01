<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * Checkout method.
     *
     * @var string
     */
    protected $checkoutMethod;

    /**
     * @constructor
     * @param CheckoutOnepage $checkoutOnepage
     * @param Address $shippingAddress
     * @param string $checkoutMethod
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        $checkoutMethod,
        Address $shippingAddress = null
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->shippingAddress = $shippingAddress;
        $this->checkoutMethod = $checkoutMethod;
    }

    /**
     * Fill shipping address.
     *
     * @return void
     */
    public function run()
    {
        if (!empty($this->shippingAddress)) {
            $this->checkoutOnepage->getShippingBlock()->fillShipping($this->shippingAddress);
        }
    }
}
