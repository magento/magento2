<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestCase;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Util\Command\Cli\EnvWhitelist;

/**
 * Preconditions:
 * 1. Create product according to dataset
 * 2. Add products to cart
 *
 * Steps:
 * 1. Open Frontend
 * 2. Click on mini shopping cart icon
 * 3. Click Delete
 * 4. Click Ok
 * 5. Perform all assertions
 *
 * @group Mini_Shopping_Cart
 * @ZephyrId MAGETWO-29104
 */
class DeleteProductFromMiniShoppingCartTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const SEVERITY = 'S0';
    /* end tags */

    /**
     * Cms index page
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Checkout cart page
     *
     * @var CheckoutCart
     */
    protected $cartPage;

    /**
     * DomainWhitelist CLI
     *
     * @var EnvWhitelist
     */
    private $envWhitelist;

    /**
     * Prepare test data
     *
     * @param CmsIndex $cmsIndex
     * @param CheckoutCart $cartPage
     * @param EnvWhitelist $envWhitelist
     * @return void
     */
    public function __prepare(
        CmsIndex $cmsIndex,
        CheckoutCart $cartPage,
        EnvWhitelist $envWhitelist
    ) {
        $this->cmsIndex = $cmsIndex;
        $this->cartPage = $cartPage;
        $this->envWhitelist = $envWhitelist;
    }

    /**
     * Run test add products to shopping cart
     *
     * @param array $products
     * @param int $deletedProductIndex
     * @return array
     */
    public function test(array $products, $deletedProductIndex)
    {
        // Preconditions
        $this->envWhitelist->addHost('example.com');
        $products = $this->prepareProducts($products);
        $this->cartPage->open();
        $this->cartPage->getCartBlock()->clearShoppingCart();

        // Steps
        $this->addToCart($products);
        $this->cartPage->getMessagesBlock()->waitSuccessMessage();
        $this->removeProduct($products[$deletedProductIndex]);
        $deletedProduct = $products[$deletedProductIndex];
        unset($products[$deletedProductIndex]);

        return ['products' => $products, 'deletedProduct' => $deletedProduct];
    }

    /**
     * Create products
     *
     * @param array $productList
     * @return InjectableFixture[]
     */
    protected function prepareProducts(array $productList)
    {
        $productsStep = ObjectManager::getInstance()->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $productList]
        );

        $result = $productsStep->run();
        return $result['products'];
    }

    /**
     * Add products to cart
     *
     * @param array $products
     * @return void
     */
    protected function addToCart(array $products)
    {
        $addToCartStep = ObjectManager::getInstance()->create(
            \Magento\Checkout\Test\TestStep\AddProductsToTheCartStep::class,
            ['products' => $products]
        );
        $addToCartStep->run();
    }

    /**
     * Remove product form cart
     *
     * @param FixtureInterface $product
     * @return void
     */
    protected function removeProduct(FixtureInterface $product)
    {
        $this->cmsIndex->open();
        $this->cmsIndex->getCartSidebarBlock()->openMiniCart();
        $this->cmsIndex->getCartSidebarBlock()->getCartItem($product)->removeItemFromMiniCart();
    }

    /**
     * Clean data after running test.
     *
     * @return void
     */
    protected function tearDown()
    {
        $this->envWhitelist->removeHost('example.com');
    }
}
