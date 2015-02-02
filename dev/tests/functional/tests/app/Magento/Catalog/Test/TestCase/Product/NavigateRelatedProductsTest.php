<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Test Flow:
 *
 * Preconditions:
 * 1. Create products.
 * 2. Assign promoted products.
 *
 * Steps:
 * 1. Navigate through related products.
 * 2. Add last product with related products to cart.
 * 3. Verify checkout cart.
 *
 * @ZephyrId MAGETWO-12392
 * @group Cross-sells_(MX)
 */
class NavigateRelatedProductsTest extends AbstractProductPromotedProductsTest
{
    /* tags */
    const TEST_TYPE = 'acceptance_test';
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Selectable data of products.
     *
     * @var array
     */
    protected $selectable = [];

    /**
     * Checkout cart page.
     *
     * @var CheckoutCart
     */
    protected $checkoutCart;

    /**
     * Run test navigate related products.
     *
     * @param string $products
     * @param string $selectable
     * @param string $promotedProducts
     * @param string $steps
     * @param string $assert
     * @param CheckoutCart $checkoutCart
     * @return void
     */
    public function test(
        $products,
        $selectable,
        $promotedProducts,
        $steps,
        $assert,
        CheckoutCart $checkoutCart
    ) {
        // Preconditions
        $this->createProducts($products);
        $this->assignPromotedProducts($promotedProducts, 'related_products');
        $this->parseSelectable($selectable);

        // Steps
        $this->checkoutCart = $checkoutCart;
        $steps = $this->parseSteps($steps);
        $assert = $this->parseAssert($assert);
        $initialProductName = array_shift($steps);
        $initialProduct = $this->products[$initialProductName];
        $initialAssert = $assert[$initialProductName];
        $lastProductName = end($steps);
        $lastProduct = $this->products[$lastProductName];
        $lastAssert = $assert[$lastProductName];
        $checkoutProducts = [];

        // Clear shopping cart
        $this->checkoutCart->open();
        $this->checkoutCart->getCartBlock()->clearShoppingCart();

        // Navigate through related products
        $this->browser->open($_ENV['app_frontend_url'] . $initialProduct->getUrlKey() . '.html');
        $this->assertRelatedSection($initialAssert);
        foreach ($steps as $productName) {
            $product = $this->products[$productName];
            $productAssert = $assert[$productName];

            $this->catalogProductView->getRelatedProductBlock()->getProductItem($product)->open();
            if (empty($productAssert)) {
                $this->assertAbsentRelatedSellSection();
            } else {
                $this->assertRelatedSection($productAssert);
            }
        }

        // Add last product with related product to cart and verify
        foreach ($lastAssert as $relatedProductName) {
            $relatedProduct = $this->products[$relatedProductName];
            $isSelect = $this->selectable[$relatedProductName];

            if ('yes' == $isSelect) {
                $checkoutProducts[] = $relatedProduct;
                $this->catalogProductView->getRelatedProductBlock()->getProductItem($relatedProduct)->select();
            }
        }
        $checkoutProducts[] = $lastProduct;
        $this->catalogProductView->getViewBlock()->addToCart($lastProduct);

        $this->assertCheckoutCart($checkoutProducts);
    }

    /**
     * Parse selectable data.
     *
     * @param string $selectable
     * @return void
     */
    protected function parseSelectable($selectable)
    {
        $list = array_map('trim', explode(',', $selectable));

        foreach ($list as $item) {
            list($productName, $isSelectable) = array_map('trim', explode(':', $item));
            $this->selectable[$productName] = $isSelectable;
        }
    }

    /**
     * Assert that absent related products section.
     *
     * @return void
     */
    protected function assertAbsentRelatedSellSection()
    {
        \PHPUnit_Framework_Assert::assertFalse(
            $this->catalogProductView->getRelatedProductBlock()->isVisible(),
            "Related section is present."
        );
    }

    /**
     * Assert that correctly display related products section.
     *
     * @param array $promotedProductNames
     * @return void
     */
    protected function assertRelatedSection(array $promotedProductNames)
    {
        $fixtureData = [];
        $pageData = [];

        foreach ($promotedProductNames as $promotedProductName) {
            $productName = $this->products[$promotedProductName]->getName();
            $fixtureData[$productName] = $this->selectable[$promotedProductName];
        }
        foreach ($this->catalogProductView->getRelatedProductBlock()->getProducts() as $productItem) {
            $pageProductName = $productItem->getProductName();
            $pageData[$pageProductName] = $productItem->isSelectable() ? 'yes' : 'no';
        }

        asort($fixtureData);
        asort($pageData);
        \PHPUnit_Framework_Assert::assertEquals($pageData, $fixtureData, 'Wrong display related section.');
    }

    /**
     * Verify checkout cart.
     *
     * @param array $checkoutProducts
     * @return void
     */
    protected function assertCheckoutCart(array $checkoutProducts)
    {
        $this->checkoutCart->open();

        foreach ($checkoutProducts as $product) {
            \PHPUnit_Framework_Assert::assertTrue(
                $this->checkoutCart->getCartBlock()->getCartItem($product)->isVisible(),
                "Product {$product->getName()} absent in cart."
            );
        }
    }
}
