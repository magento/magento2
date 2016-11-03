<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Customer\Test\Fixture\Address;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Create customer custom attribute step.
 */
class AddNewShippingAddressStep implements TestStepInterface
{
    /**
     * Checkout One page.
     *
     * @var CheckoutOnepage
     */
    private $checkoutOnepage;

    /**
     * Shipping Address fixture.
     *
     * @var Address
     */
    private $address;

    /**
     * @constructor
     * @param CheckoutOnepage $checkoutOnepage
     * @param Address|null $address [optional]
     */
    public function __construct(CheckoutOnepage $checkoutOnepage, Address $address = null)
    {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->address = $address;
    }

    /**
     * Create customer account.
     *
     * @return void
     */
    public function run()
    {
        $shippingBlock = $this->checkoutOnepage->getShippingBlock();
        $shippingBlock->clickOnNewAddressButton();
        if ($this->address) {
            $shippingBlock->getAddressModalBlock()->fill($this->address);
        }
        $shippingBlock->getAddressModalBlock()->save();
    }
}
