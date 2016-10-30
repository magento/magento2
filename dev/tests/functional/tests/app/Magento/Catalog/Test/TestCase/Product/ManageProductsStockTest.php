<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Set Configuration
 * 2. Create products according to dataset
 *
 * Steps:
 * 1. Open product on frontend
 * 2. Add product to cart if needed
 * 3. Perform all assertions
 *
 * @group Inventory
 * @ZephyrId MAGETWO-29543
 */
class ManageProductsStockTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Configuration data.
     *
     * @var string
     */
    protected $configData;

    /**
     * Setup configuration.
     *
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(FixtureFactory $fixtureFactory)
    {
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Manage products stock.
     *
     * @param CatalogProductSimple $product
     * @param string $notToCart
     * @return mixed
     */
    public function test(CatalogProductSimple $product, $skipAddingToCart = null, $configData = null)
    {
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();

        // Preconditions
        $product->persist();

        // Steps
        if(!$skipAddingToCart) {
            $this->objectManager->create(
                \Magento\Checkout\Test\TestStep\AddProductsToTheCartStep::class,
                ['products' => [$product]]
            )->run();

            $cart['data']['items'] = ['products' => [$product]];

            return ['cart' => $this->fixtureFactory->createByCode('cart', $cart)];
        }

    }

    /**
     * Set default configuration.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
