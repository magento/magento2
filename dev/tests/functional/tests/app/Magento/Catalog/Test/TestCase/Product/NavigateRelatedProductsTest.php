<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Fixture\InjectableFixture;

/**
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
 * @group Related_Products
 */
class NavigateRelatedProductsTest extends AbstractProductPromotedProductsTest
{
    /* tags */
    const TEST_TYPE = 'acceptance_test, extended_acceptance_test';
    const MVP = 'yes';
    /* end tags */

    /**
     * Products to verify.
     *
     * @var array
     */
    protected $productsToVerify;

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
     * @param string $navigateProductsOrder
     * @param string $productsToVerify
     * @param CheckoutCart $checkoutCart
     * @return void
     */
    public function test(
        $products,
        $selectable,
        $promotedProducts,
        $navigateProductsOrder,
        $productsToVerify,
        CheckoutCart $checkoutCart
    ) {
        // Preconditions
        $this->createProducts($products);
        $this->assignPromotedProducts($promotedProducts, 'related_products');
        $this->parseSelectable($selectable);

        // Initialization
        $this->checkoutCart = $checkoutCart;
        $this->productsToVerify = $this->parseProductsToVerify($productsToVerify);
        $navigateProductsOrder = $this->parseNavigateProductsOrder($navigateProductsOrder);
        $initialProductName = array_shift($navigateProductsOrder);
        $initialProduct = $this->products[$initialProductName];
        $lastProductName = end($navigateProductsOrder);
        $lastProduct = $this->products[$lastProductName];

        // Steps
        // Clear shopping cart
        $this->checkoutCart->open();
        $this->checkoutCart->getCartBlock()->clearShoppingCart();

        // Navigate through related products
        $this->browser->open($_ENV['app_frontend_url'] . $initialProduct->getUrlKey() . '.html');
        $this->assertRelatedSection($initialProductName);
        foreach ($navigateProductsOrder as $productShortName) {
            $this->navigate($productShortName);
        }

        // Add last product with related product to cart and verify
        $checkoutProducts = $this->selectRelatedProducts($lastProductName);
        $checkoutProducts[] = $lastProduct;
        $this->catalogProductView->getViewBlock()->addToCart($lastProduct);
        $this->catalogProductView->getMessagesBlock()->waitSuccessMessage();
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
     * Return related products to verify for specified product.
     *
     * @param string $product
     * @return InjectableFixture[]
     */
    protected function getProductsToVerify($product)
    {
        $shortNames = $this->productsToVerify[$product];
        $products = [];

        foreach ($shortNames as $shortName) {
            $products[$shortName] = $this->products[$shortName];
        }

        return $products;
    }

    /**
     * Open product in related products section and verify its promoted products.
     *
     * @param string $productShortName
     * @return void
     */
    protected function navigate($productShortName)
    {
        $product = $this->products[$productShortName];
        $this->catalogProductView->getRelatedProductBlock()->getProductItem($product)->open();

        if (empty($this->productsToVerify[$productShortName])) {
            $this->assertAbsentRelatedSellSection();
        } else {
            $this->assertRelatedSection($productShortName);
        }
    }

    /**
     * Select related products for specified product.
     *
     * @param string $product
     * @return InjectableFixture[]
     */
    protected function selectRelatedProducts($product)
    {
        $selected = [];

        foreach ($this->productsToVerify[$product] as $productShortName) {
            $productToVerify = $this->products[$productShortName];
            $isSelect = $this->selectable[$productShortName];

            if ('yes' == $isSelect) {
                $this->catalogProductView->getRelatedProductBlock()->getProductItem($productToVerify)->select();
                $selected[] = $productToVerify;
            }
        }

        return $selected;
    }

    /**
     * Assert that related products section is absent.
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
     * Assert that related products section is displayed correctly.
     *
     * @param string $product
     * @return void
     */
    protected function assertRelatedSection($product)
    {
        $productsToVerify = $this->getProductsToVerify($product);
        $fixtureData = [];
        $pageData = [];

        foreach ($productsToVerify as $shortName => $product) {
            $productName = $product->getName();
            $fixtureData[$productName] = $this->selectable[$shortName];
        }
        foreach ($this->catalogProductView->getRelatedProductBlock()->getProducts() as $productItem) {
            $pageProductName = $productItem->getProductName();
            $pageData[$pageProductName] = $productItem->isSelectable() ? 'yes' : 'no';
        }

        asort($fixtureData);
        asort($pageData);
        \PHPUnit_Framework_Assert::assertEquals(
            $pageData,
            $fixtureData,
            'Wrong products are displayed in related section.'
        );
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
