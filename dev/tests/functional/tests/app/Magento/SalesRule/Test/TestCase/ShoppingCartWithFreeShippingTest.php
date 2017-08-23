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
 * 1. Go to storefront
 * 2. Add product to shopping cart
 * 3. Go to shopping cart page
 * 4. Perform asserts.
 *
 * @group Shopping_Cart_Price_Rules
 * @ZephyrId MAGETWO-69066
 */
class ShoppingCartWithFreeShippingTest extends \Magento\Mtf\TestCase\Injectable
{
    /**
     * Test step factory.
     *
     * @var \Magento\Mtf\TestStep\TestStepFactory
     */
    private $testStepFactory;

    /**
     * Inject data.
     *
     * @param \Magento\Mtf\TestStep\TestStepFactory $testStepFactory
     * @return void
     */
    public function __inject(
        \Magento\Mtf\TestStep\TestStepFactory $testStepFactory
    ) {
        $this->testStepFactory = $testStepFactory;
    }

    /**
     * Test sales rule with free shipping applied by product weight.
     *
     * @param \Magento\SalesRule\Test\Fixture\SalesRule $salesRule
     * @param \Magento\Catalog\Test\Fixture\CatalogProductSimple $product
     * @param \Magento\Checkout\Test\Fixture\Cart $cart
     * @return void
     */
    public function testRuleWithFreeShippingByWeight(
        \Magento\SalesRule\Test\Fixture\SalesRule $salesRule,
        \Magento\Catalog\Test\Fixture\CatalogProductSimple $product,
        \Magento\Checkout\Test\Fixture\Cart $cart
    ) {
        $salesRule->persist();
        $product->persist();

        $this->testStepFactory->create(
            \Magento\Checkout\Test\TestStep\AddProductsToTheCartStep::class,
            ['products' => [$product]]
        )->run();

        $this->testStepFactory->create(
            \Magento\Checkout\Test\TestStep\EstimateShippingAndTaxStep::class,
            ['products' => [$product], 'cart' => $cart]
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
