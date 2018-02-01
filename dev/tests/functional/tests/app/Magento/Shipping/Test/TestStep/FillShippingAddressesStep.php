<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\TestStep;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Customer\Test\Fixture\Address;
use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Shipping\Test\Constraint\AssertCityBasedShippingRateChanged;

/**
 * Fill shipping addresses and assert rates reloading.
 */
class FillShippingAddressesStep implements TestStepInterface
{
    /**
     * Onepage checkout page.
     *
     * @var CheckoutOnepage
     */
    private $checkoutOnepage;

    /**
     * Address fixture.
     *
     * @var Address[]
     */
    private $shippingAddresses;

    /**
     * Assert City based Shipping rate.
     *
     * @var array
     */
    private $assertRate;

    /**
     * @var array
     */
    private $isShippingAvailable;

    /**
     * Shipping method.
     *
     * @var array
     */
    private $shippingMethod;

    /**
     * @param CheckoutOnepage $checkoutOnepage
     * @param FixtureFactory $fixtureFactory
     * @param AssertCityBasedShippingRateChanged $assertRate
     * @param array $shippingMethod
     * @param array $shippingAddresses
     * @param array $clearShippingAddress
     * @param array $isShippingAvailable
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        FixtureFactory $fixtureFactory,
        AssertCityBasedShippingRateChanged $assertRate,
        array $shippingMethod,
        array $shippingAddresses,
        array $clearShippingAddress,
        array $isShippingAvailable
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->assertRate = $assertRate;

        foreach ($shippingAddresses as $address) {
            $data = array_merge($clearShippingAddress, $address);
            $this->shippingAddresses[] = $fixtureFactory->createByCode('address', ['data' => $data]);
        }
        $this->isShippingAvailable = $isShippingAvailable;
        $this->shippingMethod = $shippingMethod;
    }

    /**
     * Fill shipping address and assert if the shipping rates is reloaded.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->shippingAddresses as $key => $shippingAddress) {
            $this->checkoutOnepage->getShippingBlock()->fill($shippingAddress);
            $this->assertRate->processAssert(
                $this->checkoutOnepage,
                $this->shippingMethod,
                $this->isShippingAvailable[$key]
            );
        }
    }
}
