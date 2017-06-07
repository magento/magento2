<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Customer\Test\Fixture\Address;
use Magento\Mtf\Fixture\FixtureFactory;
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
     * Factory responsible for creating fixtures.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Shipping Address fixture.
     *
     * @var Address
     */
    private $address;

    /**
     * @param CheckoutOnepage $checkoutOnepage
     * @param FixtureFactory $fixtureFactory
     * @param Address|string $address
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        FixtureFactory $fixtureFactory,
        $address
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->fixtureFactory = $fixtureFactory;
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
        if (is_string($this->address)) {
            $this->address = $this->fixtureFactory->create(
                Address::class,
                ['dataset' => $this->address]
            );
        }

        if ($this->address instanceof Address) {
            $shippingBlock->getAddressModalBlock()->fill($this->address);
        }
        $shippingBlock->getAddressModalBlock()->save();
    }
}
