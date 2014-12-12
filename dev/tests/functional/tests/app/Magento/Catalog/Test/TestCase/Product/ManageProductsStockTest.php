<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Mtf\Fixture\FixtureFactory;
use Mtf\ObjectManager;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for ManageProductsStock
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Set Configuration:
 *      - Display OutOfStock = Yes
 *      - Backorders - Allow Qty below = 0
 * 2. Create products according to dataSet
 *
 * Steps:
 * 1. Open product on frontend
 * 2. Add product to cart
 * 3. Perform all assertions
 *
 * @group Inventory_(MX)
 * @ZephyrId MAGETWO-29543
 */
class ManageProductsStockTest extends Injectable
{
    /**
     * Fixture factory
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Object manager
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Setup configuration
     *
     * @param ObjectManager $objectManager
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __prepare(ObjectManager $objectManager, FixtureFactory $fixtureFactory)
    {
        $this->objectManager = $objectManager;
        $this->fixtureFactory = $fixtureFactory;
        $setupConfigurationStep = $objectManager->create(
            'Magento\Core\Test\TestStep\SetupConfigurationStep',
            ['configData' => "display_out_of_stock,backorders_allow_qty_below"]
        );
        $setupConfigurationStep->run();
    }

    /**
     * Manage products stock
     *
     * @param CatalogProductSimple $product
     * @return array
     */
    public function test(CatalogProductSimple $product)
    {
        // Preconditions
        $product->persist();

        // Steps
        $addProductsToTheCartStep = $this->objectManager->create(
            'Magento\Checkout\Test\TestStep\AddProductsToTheCartStep',
            ['products' => [$product]]
        );
        $addProductsToTheCartStep->run();

        $cart['data']['items'] = ['products' => [$product]];
        return ['cart' => $this->fixtureFactory->createByCode('cart', $cart)];
    }

    /**
     * Set default configuration
     *
     * @return void
     */
    public static function tearDownAfterClass()
    {
        $setupConfigurationStep = ObjectManager::getInstance()->create(
            'Magento\Core\Test\TestStep\SetupConfigurationStep',
            ['configData' => "display_out_of_stock,backorders_allow_qty_below", 'rollback' => true]
        );
        $setupConfigurationStep->run();
    }
}
