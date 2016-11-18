<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Constraint\AssertCityBasedShippingRateChanged;
use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Customer\Test\Fixture\Address;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Fill shipping addresses step.
 */
class FillShippingAddressesStep implements TestStepInterface
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
    protected $shippingAddresses;

    /**
     *
     * Assert City based Shipping rate.
     * @var
     */
    protected $assertRate;

    /**
     * @constructor
     * @param CheckoutOnepage $checkoutOnepage
     * @param FixtureFactory $fixtureFactory
     * @param AssertCityBasedShippingRateChanged $assertRate
     * @param array $shippingAddresses
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        FixtureFactory $fixtureFactory,
        AssertCityBasedShippingRateChanged $assertRate,
        array $shippingAddresses = []
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->assertRate = $assertRate;

        foreach ($shippingAddresses as $address) {
            $this->shippingAddresses[] = $fixtureFactory->createByCode('address', ['dataset' => $address['dataset']]);
        }
    }

    /**
     * Fill shipping address.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->shippingAddresses as $shippingAddress) {
            $this->checkoutOnepage->getShippingBlock()->fill($shippingAddress);
            $this->assertRate->processAssert($this->checkoutOnepage);
        }
    }
}
