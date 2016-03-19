<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Preconditions:
 * 1. Create products.
 * 2. Assign promoted products.
 *
 * Steps:
 * 1. Add initial products to cart.
 * 2. Verify Cross-sell block on checkout page.
 *
 * @ZephyrId MAGETWO-12390
 * @group Cross-sells_(MX)
 */
class AddToCartCrossSellTest extends AbstractProductPromotedProductsTest
{
    /* tags */
    const TEST_TYPE = 'acceptance_test';
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Frontend page.
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Checkout page.
     *
     * @var CheckoutCart
     */
    protected $checkoutCart;

    /**
     * Run test add to cart cross-sell products.
     *
     * @param string $products
     * @param string $promotedProducts
     * @param string $navigateProductsOrder
     * @param string $productsToVerify
     * @param CmsIndex $cmsIndex
     * @param CheckoutCart $checkoutCart
     * @return void
     */
    public function test(
        $products,
        $promotedProducts,
        $navigateProductsOrder,
        $productsToVerify,
        CmsIndex $cmsIndex,
        CheckoutCart $checkoutCart
    ) {
        // Preconditions
        $this->createProducts($products);
        $this->assignPromotedProducts($promotedProducts, 'cross_sell_products');

        // Initialization
        $this->cmsIndex = $cmsIndex;
        $this->checkoutCart = $checkoutCart;
        $navigateProductsOrder = $this->parseNavigateProductsOrder($navigateProductsOrder);
        $productsToVerify = $this->parseProductsToVerify($productsToVerify);
        $initialProductName = array_shift($navigateProductsOrder);
        $initialProduct = $this->products[$initialProductName];
        $initialProductToVerify = $productsToVerify[$initialProductName];

        // Steps
        $this->checkoutCart->open();
        $this->checkoutCart->getCartBlock()->clearShoppingCart();

        $this->browser->open($_ENV['app_frontend_url'] . $initialProduct->getUrlKey() . '.html');
        $this->catalogProductView->getViewBlock()->addToCart($initialProduct);
        $this->catalogProductView->getMessagesBlock()->waitSuccessMessage();
        $this->assertCrossSellSection($initialProductToVerify);
        foreach ($navigateProductsOrder as $productName) {
            $this->addToCart($this->products[$productName]);

            if (empty($productsToVerify[$productName])) {
                $this->assertAbsentCrossSellSection();
            } else {
                $this->assertCrossSellSection($productsToVerify[$productName]);
            }
        }
    }

    /**
     * Add product to cart from Cross-sell section.
     *
     * @param InjectableFixture $product
     * @return void
     */
    protected function addToCart(InjectableFixture $product)
    {
        $this->checkoutCart->getCrosssellBlock()->getProductItem($product)->clickAddToCart();
        if ($this->cmsIndex->getTitleBlock()->getTitle() == $product->getName()) {
            $this->catalogProductView->getViewBlock()->addToCart($product);
        }

        $this->catalogProductView->getMessagesBlock()->waitSuccessMessage();
    }

    /**
     * Assert that cross-sell products section is absent.
     *
     * @return void
     */
    protected function assertAbsentCrossSellSection()
    {
        $this->checkoutCart->open();
        \PHPUnit_Framework_Assert::assertFalse(
            $this->checkoutCart->getCrosssellBlock()->isVisible(),
            "Cross-sell block is present."
        );
    }

    /**
     * Assert that cross-sell products section is displayed correctly.
     *
     * @param array $promotedProductNames
     * @return void
     */
    protected function assertCrossSellSection(array $promotedProductNames)
    {
        $productNames = [];
        $pageProductNames = [];

        foreach ($promotedProductNames as $promotedProductName) {
            $productNames[] = $this->products[$promotedProductName]->getName();
        }
        $this->checkoutCart->open();
        foreach ($this->checkoutCart->getCrosssellBlock()->getProducts() as $productItem) {
            $pageProductNames[] = $productItem->getProductName();
        }

        sort($productNames);
        sort($pageProductNames);
        \PHPUnit_Framework_Assert::assertEquals(
            $productNames,
            $pageProductNames,
            'Wrong products are displayed in cross-sell section.'
        );
    }
}
