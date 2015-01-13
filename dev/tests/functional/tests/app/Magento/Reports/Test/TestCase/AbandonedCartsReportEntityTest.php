<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\TestCase;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Mtf\Client\Browser;
use Mtf\Fixture\FixtureFactory;
use Mtf\TestCase\Injectable;

/**
 * Test Flow:
 * Preconditions:
 * 1. Create simple product.
 * 2. Create customer.
 * 3. Go to frontend.
 * 4. Login as customer.
 * 5. Add simple product to cart.
 * 6. Logout.
 *
 * Steps:
 * 1. Open Backend.
 * 2. Go to Reports > Abandoned Carts.
 * 3. Click "Reset Filter".
 * 4. Perform all assertions.
 *
 * @group Reports_(MX)
 * @ZephyrId MAGETWO-28558
 */
class AbandonedCartsReportEntityTest extends Injectable
{
    /**
     * Catalog Product View page.
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Browser interface.
     *
     * @var Browser
     */
    protected $browser;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Inject pages.
     *
     * @param Browser $browser
     * @param FixtureFactory $fixtureFactory
     * @param CatalogProductView $catalogProductView
     * @return void
     */
    public function __inject(
        Browser $browser,
        FixtureFactory $fixtureFactory,
        CatalogProductView $catalogProductView
    ) {
        $this->browser = $browser;
        $this->catalogProductView = $catalogProductView;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Create product and add it to cart.
     *
     * @param string $products
     * @param CustomerInjectable $customer
     * @return array
     */
    public function test($products, CustomerInjectable $customer)
    {
        $this->markTestIncomplete('Bug: MAGETWO-31737');
        // Precondition
        $products = $this->createProducts($products);
        $customer->persist();
        $this->objectManager->create(
            'Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
            ['customer' => $customer]
        )->run();
        $this->addProductsToCart($products);
        $this->objectManager->create(
            'Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep',
            ['customer' => $customer]
        )->run();

        return ['products' => $products];
    }

    /**
     * Create products.
     *
     * @param string $products
     * @return array
     */
    protected function createProducts($products)
    {
        $createProductsStep = $this->objectManager->create(
            'Magento\Catalog\Test\TestStep\CreateProductsStep',
            ['products' => $products]
        );

        return $createProductsStep->run()['products'];
    }

    /**
     * Add products to cart.
     *
     * @param array $products
     * @return void
     */
    protected function addProductsToCart(array $products)
    {
        $addProductsToCart = $this->objectManager->create(
            'Magento\Checkout\Test\TestStep\AddProductsToTheCartStep',
            ['products' => $products]
        );
        $addProductsToCart->run();
    }
}
