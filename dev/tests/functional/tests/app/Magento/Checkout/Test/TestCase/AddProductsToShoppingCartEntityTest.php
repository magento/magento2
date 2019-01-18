<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestCase;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Backend\Test\Page\Adminhtml\SystemConfigEdit;

/**
 * Preconditions:
 * 1. All type products is created
 *
 * Steps:
 * 1. Navigate to frontend
 * 2. Open test product page
 * 3. Add to cart test product
 * 4. Perform all asserts
 *
 * @group Shopping_Cart_(CS)
 * @ZephyrId MAGETWO-25382
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddProductsToShoppingCartEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    /* end tags */

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Catalog product view page.
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Checkout cart page.
     *
     * @var CheckoutCart
     */
    protected $cartPage;

    /**
     * Test step creation factory.
     *
     * @var TestStepFactory
     */
    protected $testStepFactory;

    /**
     * Configuration data.
     *
     * @var string
     */
    protected $configData;

    /**
     * Prepare test data.
     *
     * @param CatalogProductView $catalogProductView
     * @param CheckoutCart $cartPage
     * @param TestStepFactory $testStepFactory
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __prepare(
        CatalogProductView $catalogProductView,
        CheckoutCart $cartPage,
        TestStepFactory $testStepFactory,
        FixtureFactory $fixtureFactory
    ) {
        $this->catalogProductView = $catalogProductView;
        $this->cartPage = $cartPage;
        $this->testStepFactory = $testStepFactory;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Run test add products to shopping cart.
     *
     * @param string $productsData
     * @param array $cart
     * @param string|null $configData [optional]
     * @return array
     */
    public function test($productsData, array $cart, $configData = null)
    {
        // Preconditions
        $this->configData = $configData;

        $this->testStepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $products = $this->prepareProducts($productsData);
        $this->setupConfiguration();

        // Steps
        $this->addToCart($products);

        $cart['data'] = (array)$cart['data'];
        $cart['data']['items'] = ['products' => $products];
        return [
            'cart' => $this->fixtureFactory->createByCode('cart', $cart),
            'product' => array_shift($products),
            'products' => $products,
        ];
    }

    /**
     * Create products.
     *
     * @param string $productList
     * @return array
     */
    protected function prepareProducts($productList)
    {
        $addToCartStep = $this->testStepFactory->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $productList]
        );

        $result = $addToCartStep->run();
        return $result['products'];
    }

    /**
     * Add products to cart.
     *
     * @param array $products
     * @return void
     */
    protected function addToCart(array $products)
    {
        $addToCartStep = $this->testStepFactory->create(
            \Magento\Checkout\Test\TestStep\AddProductsToTheCartStep::class,
            ['products' => $products]
        );
        $addToCartStep->run();
    }

    /**
     * Setup configuration.
     *
     * @return void
     */
    private function setupConfiguration()
    {
        $this->testStepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
    }

    protected function tearDown()
    {
        $this->testStepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->cleanup();
    }
}
