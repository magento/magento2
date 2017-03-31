<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Test\TestCase;

use Magento\Config\Test\TestStep\SetupConfigurationStep;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Check 'Show Swatches In Product List' configuration option impact on Category page
 *
 * @group Swatches (MX)
 * @ZephyrId MAGETWO-66928
 */
class CheckShowSwatchesInProductListOptionImpactTest extends Injectable
{
    /**
     * Factory for creation SetupConfigurationStep.
     *
     * @var TestStepFactory
     */
    private $testStepFactory;

    /**
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function __prepare(
        FixtureFactory $fixtureFactory
    ) {
        $product = $fixtureFactory->createByCode('configurableProduct', ['dataset' => 'product_with_text_swatch']);
        $product->persist();
        return ['product' => $product];
    }

    /**
     * @param TestStepFactory $testStepFactory
     */
    public function __inject(
        TestStepFactory $testStepFactory
    ) {
        $this->testStepFactory = $testStepFactory;
    }

    /**
     * Set value for 'Show Swatches in Product List'
     *
     * @param string $configData
     * @return void
     */
    public function test($configData)
    {
        $this->testStepFactory->create(
            SetupConfigurationStep::class,
            ['configData' => $configData, 'flushCache' => true]
        )->run();
    }
}
