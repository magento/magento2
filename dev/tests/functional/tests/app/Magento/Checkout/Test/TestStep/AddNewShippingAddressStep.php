<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Customer\Test\Fixture\Address;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Add new shipping address on checkout step.
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
     * Save Shipping Address.
     *
     * @var boolean
     */
    private $save;

    /**
     * @constructor
     * @param CheckoutOnepage $checkoutOnepage
     * @param Address|null $shippingAddress [optional]
     * @param boolean $save [optional]
     */
    public function __construct(CheckoutOnepage $checkoutOnepage, Address $shippingAddress = null, $save = true)
    {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->address = $shippingAddress;
        $this->save = $save;
    }

    /**
     * Add new shipping address.
     *
     * @return array
     */
    public function run()
    {
        $shippingBlock = $this->checkoutOnepage->getShippingBlock();
        $shippingBlock->clickOnNewAddressButton();
        if ($this->address) {
            $shippingBlock->getAddressModalBlock()->fill($this->address);
        }
        if ($this->save) {
            $shippingBlock->getAddressModalBlock()->save();
        } else {
            $shippingBlock->getAddressModalBlock()->cancel();
        }

        return ['shippingAddress' => $this->address];
    }
}
