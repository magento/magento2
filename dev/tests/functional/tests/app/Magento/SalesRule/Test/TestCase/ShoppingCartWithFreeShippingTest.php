<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\TestCase;

/**
 * Precondition:
 * 1. Cart Price Rule was created.
 *
 * Steps:
 * 1. Go to storefront.
 * 2. Add product to shopping cart.
 * 3. Go to shopping cart page.
 * 4. Perform asserts.
 *
 * @group Shopping_Cart_Price_Rules
 * @ZephyrId MAGETWO-64748
 */
class ShoppingCartWithFreeShippingTest extends \Magento\Mtf\TestCase\Injectable
{
    /**
     * @var \Magento\Mtf\TestStep\TestStepFactory
     */
    private $testStepFactory;

    /**
     * Factory for cteating product fixture.
     *
     * @var \Magento\Mtf\Fixture\FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Inject data.
     *
     * @param \Magento\Mtf\TestStep\TestStepFactory $testStepFactory
     * @param \Magento\Mtf\Fixture\FixtureFactory
     * @return void
     */
    public function __inject(
        \Magento\Mtf\TestStep\TestStepFactory $testStepFactory,
        \Magento\Mtf\Fixture\FixtureFactory $fixtureFactory
    ) {
        $this->testStepFactory = $testStepFactory;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Test sales rule with free shipping applied by product weight.
     *
     * @param \Magento\SalesRule\Test\Fixture\SalesRule $salesRule
     * @param string $product
     * @param \Magento\Checkout\Test\Fixture\Cart $cart
     * @return void
     */
    public function testRuleWithFreeShippingByWeight(
        \Magento\SalesRule\Test\Fixture\SalesRule $salesRule,
        $product,
        \Magento\Checkout\Test\Fixture\Cart $cart,
        array $shipping = null
    ) {
        $salesRule->persist();
        list($fixture, $dataset) = explode('::', $product);
        $product = $this->fixtureFactory->createByCode($fixture, ['dataset' => $dataset]);
        $product->persist();

        $this->testStepFactory->create(
            \Magento\Checkout\Test\TestStep\AddProductsToTheCartStep::class,
            ['products' => [$product]]
        )->run();

        $this->testStepFactory->create(
            \Magento\Checkout\Test\TestStep\EstimateShippingAndTaxStep::class,
            ['products' => [$product], 'cart' => $cart, 'shipping' => $shipping]
        )->run();
    }

    /**
     * Clear data after test.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->testStepFactory->create(\Magento\SalesRule\Test\TestStep\DeleteAllSalesRuleStep::class)->run();
    }
}
