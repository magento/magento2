<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Precondition:
 * 1. Cart Price Rule was created.
 * 2. Flat Rate and Free Shipping methods were enabled
 *
 * Steps:
 * 1. Go to storefront
 * 2. Add product to shopping cart
 * 3. Go to shopping cart page
 * 4. Select shipping method
 * 5. Perform asserts
 *
 * @group Shopping_Cart_Price_Rules
 * @ZephyrId MAGETWO-61305
 */
class ShoppingCartWithFreeShippingAndFlatRateTest extends \Magento\Mtf\TestCase\Injectable
{
    /**
     * Sales rule name.
     *
     * @var string
     */
    private $salesRuleName;

    /**
     * FixtureFactory
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * TestStepFactory
     *
     * @var \Magento\Mtf\TestStep\TestStepFactory
     */
    private $testStepFactory;

    /**
     * Configuration setting.
     *
     * @var string
     */
    private $configData;

    /**
     * Name of custom attribute.
     *
     * @var CatalogProductAttribute
     */
    private $attribute;

    /**
     * Inject data
     *
     * @param \Magento\Mtf\TestStep\TestStepFactory $testStepFactory
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(
        \Magento\Mtf\TestStep\TestStepFactory $testStepFactory,
        FixtureFactory $fixtureFactory
    ) {
        $this->testStepFactory = $testStepFactory;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Test sales rule with free shipping applied by cart price rule
     *
     * @param \Magento\Catalog\Test\Fixture\CatalogProductAttribute $productAttribute
     * @param array $shipping
     * @param string $configData
     * @param int $freeShipping
     * @return void
     */
    public function testRuleWithFreeShippingAndFlatRate(
        \Magento\Catalog\Test\Fixture\CatalogProductAttribute $productAttribute,
        array $shipping,
        string $configData,
        int $freeShipping
    ) {
        $productAttribute->persist();
        $this->testStepFactory->create(
            \Magento\Catalog\Test\TestStep\AddAttributeToAttributeSetStep::class,
            ['attribute' => $productAttribute]
        )->run();

        $this->testStepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $configData]
        )->run();

        $cond = '{Product attribute combination|FOUND|ALL|:[[' . $productAttribute->getAttributeCode() . '|is|Yes]]}';
        $salesRule = $this->fixtureFactory->createByCode(
            'salesRule',
            [
                'dataset' => 'rule_with_freeshipping',
                'data' => [
                    'conditions_serialized' => $cond
                ]
            ]
        );
        $this->testStepFactory->create(
            \Magento\SalesRule\Test\TestStep\CreateSalesRuleThroughAdminStep::class,
            ['salesRule' => $salesRule]
        )->run();

        $customAttribute = ['value' => $freeShipping, 'attribute' => $productAttribute];
        $product = $this->fixtureFactory->createByCode(
            'catalogProductSimple',
            [
                'dataset' => 'default',
                'data' => [
                    'custom_attribute' => $customAttribute
                ],
            ]
        );
        $product->persist();

        // Set values for deletion
        $this->salesRuleName = $salesRule->getName();
        $this->configData = $configData;
        $this->attribute = $productAttribute;

        $this->testStepFactory->create(
            \Magento\Checkout\Test\TestStep\AddProductsToTheCartStep::class,
            ['products' => [$product]]
        )->run();

        $this->testStepFactory->create(
            \Magento\Checkout\Test\TestStep\FillShippingMethodOnEstimateStep::class,
            ['shipping' => $shipping]
        )->run();
    }

    /**
     * Clear data after test.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->testStepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();

        $this->testStepFactory->create(
            \Magento\SalesRule\Test\TestStep\DeleteSalesRulesStep::class,
            ['salesRules' => [$this->salesRuleName]]
        )->run();

        $this->testStepFactory->create(
            \Magento\Catalog\Test\TestStep\DeleteAttributeStep::class,
            ['attribute' => $this->attribute]
        )->run();
    }
}
