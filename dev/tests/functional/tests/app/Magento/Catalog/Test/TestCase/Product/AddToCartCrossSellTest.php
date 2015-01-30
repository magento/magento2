<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Catalog\Test\Constraint\AssertProductSaveMessage;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Checkout\Test\Constraint\AssertAddedProductToCartSuccessMessage;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test Flow:
 *
 * Preconditions:
 * 1. Create products.
 * 2. Assign promoted products.
 *
 * Steps:
 * 1. Add some products to cart.
 * 2. Verify Cross-sell block on checkout page.
 *
 * @group Cross-sells_(MX)
 */
class AddToCartCrossSellTest extends Injectable
{
    /* tags */
    const TEST_TYPE = 'acceptance_test';
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Interface Browser.
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * Fixture Factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Suite products.
     *
     * @var FixtureInterface[]
     */
    protected $products = [];

    /**
     * Catalog product index page in backend.
     *
     * @var CatalogProductIndex
     */
    protected $catalogProductIndex;

    /**
     * Catalog product edit page in backend.
     *
     * @var CatalogProductEdit
     */
    protected $catalogProductEdit;

    /**
     * Catalog product view page in frontend.
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Checkout page.
     *
     * @var CheckoutCart
     */
    protected $checkoutCart;

    /**
     * Assert success message is displayed after product save.
     *
     * @var AssertProductSaveMessage
     */
    protected $assertProductSaveMessage;

    /**
     * Assert success message is appeared after add product to cart.
     *
     * @var AssertAddedProductToCartSuccessMessage
     */
    protected $assertAddedProductToCartSuccessMessage;

    /**
     * Prepare data.
     *
     * @param BrowserInterface $browser
     * @param FixtureFactory $fixtureFactory
     * @param AssertProductSaveMessage $assertProductSaveMessage
     * @param AssertAddedProductToCartSuccessMessage $assertAddedProductToCartSuccessMessage
     * @return void
     */
    public function __prepare(
        BrowserInterface $browser,
        FixtureFactory $fixtureFactory,
        AssertProductSaveMessage $assertProductSaveMessage,
        AssertAddedProductToCartSuccessMessage $assertAddedProductToCartSuccessMessage
    ) {
        $this->browser = $browser;
        $this->fixtureFactory = $fixtureFactory;
        $this->assertProductSaveMessage = $assertProductSaveMessage;
        $this->assertAddedProductToCartSuccessMessage = $assertAddedProductToCartSuccessMessage;
    }

    /**
     * Inject data.
     *
     * @param CatalogProductIndex $catalogProductIndex
     * @param CatalogProductEdit $catalogProductEdit
     * @param CatalogProductView $catalogProductView
     * @param CheckoutCart $checkoutCart
     * @return void
     */
    public function __inject(
        CatalogProductIndex $catalogProductIndex,
        CatalogProductEdit $catalogProductEdit,
        CatalogProductView $catalogProductView,
        CheckoutCart $checkoutCart
    ) {
        $this->catalogProductIndex = $catalogProductIndex;
        $this->catalogProductEdit = $catalogProductEdit;
        $this->catalogProductView = $catalogProductView;
        $this->checkoutCart = $checkoutCart;
    }

    /**
     * Run test add to cart cross-sell products.
     *
     * @param string $products
     * @param string $promotedProducts
     * @param string $order
     * @param string $crossSell
     * @return void
     */
    public function test($products, $promotedProducts, $order, $crossSell)
    {
        // Preconditions
        $this->createProducts($products);
        $this->assignPromotedProducts($promotedProducts);

        // Steps
        $this->createOrder($order);
        $this->verify($crossSell);
    }

    /**
     * Create products.
     *
     * @param string $products
     * @return void
     */
    protected function createProducts($products)
    {
        $list = array_map('trim', explode(',', $products));

        foreach ($list as $item) {
            list($productName, $fixtureCode, $dataSet) = array_map('trim', explode('::', $item));
            $product = $this->fixtureFactory->createByCode($fixtureCode, ['dataSet' => $dataSet]);

            $product->persist();
            $this->products[$productName] = $product;
        }
    }

    /**
     * Assigning promoted products.
     *
     * @param string $promotedProducts
     * @return void
     */
    protected function assignPromotedProducts($promotedProducts)
    {
        $promotedProducts = $this->parsePromotedProducts($promotedProducts);

        foreach ($promotedProducts as $productName => $assignedNames) {
            $initialProduct = $this->products[$productName];
            $filter = ['sku' => $initialProduct->getSku()];
            $assignedProducts = [];

            foreach ($assignedNames as $assignedName) {
                $assignedProducts[] = $this->products[$assignedName];
            }

            $product = $this->fixtureFactory->create(
                get_class($initialProduct),
                [
                    'data' => [
                        'cross_sell_products' => [
                            'products' => $assignedProducts
                        ]
                    ]
                ]
            );
            $this->catalogProductIndex->open();
            $this->catalogProductIndex->getProductGrid()->searchAndOpen($filter);
            $this->catalogProductEdit->getProductForm()->fill($product);
            $this->catalogProductEdit->getFormPageActions()->save();
            $this->assertProductSaveMessage->processAssert($this->catalogProductEdit);
        }
    }

    /**
     * Parse promoted products.
     *
     * @param string $promotedProducts
     * @return array
     */
    protected function parsePromotedProducts($promotedProducts)
    {
        $list = array_map('trim', explode(';', $promotedProducts));
        $result = [];

        foreach ($list as $item) {
            list($productName, $promotedNames) = array_map('trim', explode(':', $item));
            $result[$productName] = array_map('trim', explode(',', $promotedNames));
        }

        return $result;
    }

    /**
     * Create order.
     *
     * @param string $order
     * @return void
     */
    protected function createOrder($order)
    {
        $products = $this->prepareOrder($order);
        $initialProduct = array_shift($products);

        $this->checkoutCart->open();
        $this->checkoutCart->getCartBlock()->clearShoppingCart();

        $this->browser->open($_ENV['app_frontend_url'] . $initialProduct->getUrlKey() . '.html');
        $this->catalogProductView->getViewBlock()->addToCart($initialProduct);
        $this->assertAddedProductToCartSuccessMessage->processAssert($this->checkoutCart, $initialProduct);
        foreach ($products as $product) {
            $this->addToCartFromCrossSell($product);
        }
    }

    /**
     * Convert short names products to products entities.
     *
     * @param string $order
     * @return array
     */
    protected function prepareOrder($order)
    {
        $productNames = array_map('trim', explode(',', $order));
        $products = [];

        foreach ($productNames as $productName) {
            $products[] = $this->products[$productName];
        }

        return $products;
    }

    /**
     * Add product from cross-sell block to cart.
     *
     * @param FixtureInterface $product
     * @return void
     */
    protected function addToCartFromCrossSell(FixtureInterface $product)
    {
        $productItem = $this->checkoutCart->getCrosssellBlock()->getProductItem($product);

        \PHPUnit_Framework_Assert::assertTrue(
            $productItem->isVisible(),
            "Product {$product->getName()} is absent in cross-sell block."
        );

        $productItem->clickAddToCart();
        if (false !== strpos($this->browser->getUrl(), $product->getUrlKey())) {
            $this->catalogProductView->getViewBlock()->addToCart($product);
        }
        $this->assertAddedProductToCartSuccessMessage->processAssert($this->checkoutCart, $product);
    }

    /**
     * Verify cross-sell block.
     *
     * @param string $crossSell
     * @return void
     */
    protected function verify($crossSell)
    {
        if (empty($crossSell)) {
            \PHPUnit_Framework_Assert::assertFalse(
                $this->checkoutCart->getCrosssellBlock()->isVisible(),
                "Cross-sell block is present."
            );
            return;
        }

        $products = array_map('trim', explode(',', $crossSell));
        $productNames = [];

        foreach ($products as $shortName) {
            $productNames[] = $this->products[$shortName]->getName();
        }
        \PHPUnit_Framework_Assert::assertEquals(
            $productNames,
            $this->checkoutCart->getCrosssellBlock()->getProductNames()
        );
    }
}
