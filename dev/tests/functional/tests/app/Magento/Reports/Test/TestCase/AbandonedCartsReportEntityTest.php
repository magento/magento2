<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Catalog\Test\Page\Product\CatalogProductView;

/**
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
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Catalog Product View page.
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Browser interface.
     *
     * @var BrowserInterface
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
     * @param BrowserInterface $browser
     * @param FixtureFactory $fixtureFactory
     * @param CatalogProductView $catalogProductView
     * @return void
     */
    public function __inject(
        BrowserInterface $browser,
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
     * @param Customer $customer
     * @return array
     */
    public function test($products, Customer $customer)
    {
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
