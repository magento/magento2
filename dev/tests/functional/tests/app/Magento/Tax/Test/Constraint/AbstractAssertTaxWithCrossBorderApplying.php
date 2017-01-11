<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class AbstractAssertTaxWithCrossBorderApplying
 * Abstract class for implementing assert cross border applying
 */
abstract class AbstractAssertTaxWithCrossBorderApplying extends AbstractConstraint
{
    /**
     * Cms index page
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Catalog product page
     *
     * @var catalogCategoryView
     */
    protected $catalogCategoryView;

    /**
     * Catalog product page
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Catalog product page
     *
     * @var CheckoutCart
     */
    protected $checkoutCart;

    /**
     * Implementation assert
     *
     * @param array $actualPrices
     * @return void
     */
    abstract protected function assert($actualPrices);

    /**
     * 1. Login with each customer and get product price on category, product and cart pages
     * 2. Implementation assert
     *
     * @param CatalogProductSimple $product
     * @param CmsIndex $cmsIndex
     * @param CatalogCategoryView $catalogCategoryView
     * @param CatalogProductView $catalogProductView
     * @param CheckoutCart $checkoutCart
     * @param array $customers
     * @return void
     */
    public function processAssert(
        CatalogProductSimple $product,
        CmsIndex $cmsIndex,
        CatalogCategoryView $catalogCategoryView,
        CatalogProductView $catalogProductView,
        CheckoutCart $checkoutCart,
        array $customers
    ) {
        $this->cmsIndex = $cmsIndex;
        $this->catalogCategoryView = $catalogCategoryView;
        $this->catalogProductView = $catalogProductView;
        $this->checkoutCart = $checkoutCart;
        $actualPrices = $this->getPricesForCustomers($product, $customers);
        $this->assert($actualPrices);
    }

    /**
     * Login with each provided customer and get product prices
     *
     * @param CatalogProductSimple $product
     * @param array $customers
     * @return array
     */
    protected function getPricesForCustomers(CatalogProductSimple $product, $customers)
    {
        $prices = [];
        foreach ($customers as $customer) {
            $this->loginCustomer($customer);
            $this->openCategory($product);
            $actualPrices = [];
            $actualPrices = $this->getCategoryPrice($product, $actualPrices);
            $this->catalogCategoryView->getListProductBlock()->getProductItem($product)->open();
            $actualPrices = $this->addToCart($product, $actualPrices);
            $actualPrices = $this->getCartPrice($product, $actualPrices);
            $prices[] = $actualPrices;
            $this->clearShoppingCart();
        }
        return $prices;
    }

    /**
     * Open product category
     *
     * @param CatalogProductSimple $product
     * @return void
     */
    protected function openCategory(CatalogProductSimple $product)
    {
        $this->cmsIndex->open();
        $this->cmsIndex->getTopmenu()->selectCategoryByName($product->getCategoryIds()[0]);
    }

    /**
     * Get prices on category page
     *
     * @param FixtureInterface $product
     * @param array $actualPrices
     * @return array
     */
    protected function getCategoryPrice(FixtureInterface $product, $actualPrices)
    {
        $actualPrices['category_price_incl_tax'] =
            $this->catalogCategoryView
                ->getListProductBlock()
                ->getProductItem($product)
                ->getPriceBlock()
                ->getPriceIncludingTax();
        return $actualPrices;
    }

    /**
     * Fill options get price and add to cart
     *
     * @param CatalogProductSimple $product
     * @param array $actualPrices
     * @return array
     */
    protected function addToCart(CatalogProductSimple $product, $actualPrices)
    {
        $this->catalogProductView->getViewBlock()->fillOptions($product);
        $actualPrices['product_page_price'] =
            $this->catalogProductView->getViewBlock()->getPriceBlock()->getPrice();
        $this->catalogProductView->getViewBlock()->clickAddToCart();
        $this->catalogProductView->getMessagesBlock()->waitSuccessMessage();
        return $actualPrices;
    }

    /**
     * Get cart prices
     *
     * @param CatalogProductSimple $product
     * @param array $actualPrices
     * @return array
     */
    protected function getCartPrice(CatalogProductSimple $product, $actualPrices)
    {
        $this->checkoutCart->open();
        $actualPrices['cart_item_price_incl_tax'] =
            $this->checkoutCart->getCartBlock()->getCartItem($product)->getPriceInclTax();
        $actualPrices['cart_item_subtotal_incl_tax'] =
            $this->checkoutCart->getCartBlock()->getCartItem($product)->getSubtotalPriceInclTax();
        $actualPrices['grand_total'] =
            $this->checkoutCart->getTotalsBlock()->getGrandTotalIncludingTax();
        return $actualPrices;
    }

    /**
     * Login customer
     *
     * @param $customer
     * @return void
     */
    protected function loginCustomer($customer)
    {
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $customer]
        )->run();
    }

    /**
     * Clear shopping cart
     *
     * @return void
     */
    protected function clearShoppingCart()
    {
        $this->checkoutCart->open();
        $this->checkoutCart->getCartBlock()->clearShoppingCart();
    }
}
