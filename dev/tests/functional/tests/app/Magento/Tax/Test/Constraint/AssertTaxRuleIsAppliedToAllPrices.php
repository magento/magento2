<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Fixture\AddressInjectable;
use Mtf\Constraint\AbstractConstraint;
use Mtf\Fixture\FixtureFactory;

/**
 * Class AssertTaxRuleIsAppliedToAllPrice
 * Checks that prices on category, product and cart pages are equal to specified in dataset
 */
class AssertTaxRuleIsAppliedToAllPrices extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

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
     * Assert that specified prices are actual on category, product and cart pages
     *
     * @param CatalogProductSimple $product
     * @param array $prices
     * @param CmsIndex $cmsIndex
     * @param CatalogCategoryView $catalogCategoryView
     * @param CatalogProductView $catalogProductView
     * @param CheckoutCart $checkoutCart
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function processAssert(
        CatalogProductSimple $product,
        array $prices,
        CmsIndex $cmsIndex,
        CatalogCategoryView $catalogCategoryView,
        CatalogProductView $catalogProductView,
        CheckoutCart $checkoutCart,
        FixtureFactory $fixtureFactory
    ) {
        $this->cmsIndex = $cmsIndex;
        $this->catalogCategoryView = $catalogCategoryView;
        $this->catalogProductView = $catalogProductView;
        $this->checkoutCart = $checkoutCart;
        //Preconditions
        $address = $fixtureFactory->createByCode('addressInjectable', ['dataSet' => 'US_address_NY']);
        $shipping = ['carrier' => 'Flat Rate', 'method' => 'Fixed'];

        //Assertion steps
        $productName = $product->getName();
        $this->openCategory($product);
        $actualPrices = [];
        $actualPrices = $this->getCategoryPrices($productName, $actualPrices);
        $catalogCategoryView->getListProductBlock()->openProductViewPage($productName);
        $actualPrices = $this->getProductPagePrices($actualPrices);
        $catalogProductView->getViewBlock()->setQtyAndClickAddToCart(3);
        $actualPrices = $this->getCartPrices($product, $actualPrices);
        $this->fillEstimateBlock($address, $shipping);
        $actualPrices = $this->getTotals($actualPrices);

        //Prices verification
        \PHPUnit_Framework_Assert::assertEquals($prices, $actualPrices, 'Arrays should be equal');
    }

    /**
     * Open product category
     *
     * @param CatalogProductSimple $product
     * @return void
     */
    public function openCategory(CatalogProductSimple $product)
    {
        $this->cmsIndex->open();
        $this->cmsIndex->getTopmenu()->selectCategoryByName($product->getCategoryIds()[0]);
    }

    /**
     * Get prices on category page
     *
     * @param $productName
     * @param array $actualPrices
     * @return array
     */
    public function getCategoryPrices($productName, $actualPrices)
    {
        $actualPrices['category_price_excl_tax'] =
            $this->catalogCategoryView
                ->getListProductBlock()
                ->getProductPriceBlock($productName)
                ->getPriceExcludingTax();
        $actualPrices['category_price_incl_tax'] =
            $this->catalogCategoryView
                ->getListProductBlock()
                ->getProductPriceBlock($productName)
                ->getPriceIncludingTax();
        return $actualPrices;
    }

    /**
     * Get product view prices
     *
     * @param $actualPrices
     * @return array
     */
    public function getProductPagePrices($actualPrices)
    {
        $actualPrices['product_view_price_excl_tax'] =
            $this->catalogProductView
                ->getViewBlock()
                ->getProductPriceExcludingTax();
        $actualPrices['product_view_price_incl_tax'] =
            $this->catalogProductView
                ->getViewBlock()
                ->getProductPriceIncludingTax();
        return $actualPrices;
    }

    /**
     * Get cart prices
     *
     * @param CatalogProductSimple $product
     * @param $actualPrices
     * @return array
     */
    public function getCartPrices(CatalogProductSimple $product, $actualPrices)
    {
        $actualPrices['cart_item_subtotal_excl_tax'] =
            $this->checkoutCart->getCartBlock()->getCartItem($product)->getSubtotalPrice();
        $actualPrices['cart_item_subtotal_incl_tax'] =
            $this->checkoutCart->getCartBlock()->getCartItem($product)->getSubtotalPriceInclTax();
        $actualPrices['cart_item_price_excl_tax'] =
            $this->checkoutCart->getCartBlock()->getCartItem($product)->getPrice();
        $actualPrices['cart_item_price_incl_tax'] =
            $this->checkoutCart->getCartBlock()->getCartItem($product)->getPriceInclTax();
        return $actualPrices;
    }

    /**
     * Fill estimate block
     *
     * @param AddressInjectable $address
     * @param array $shipping
     * @return void
     */
    public function fillEstimateBlock(AddressInjectable $address, $shipping)
    {
        $this->checkoutCart->getShippingBlock()->fillEstimateShippingAndTax($address);
        $this->checkoutCart->getShippingBlock()->selectShippingMethod($shipping);
    }

    /**
     * Get totals
     *
     * @param $actualPrices
     * @return array
     */
    public function getTotals($actualPrices)
    {
        $actualPrices['subtotal_excl_tax'] = $this->checkoutCart->getTotalsBlock()->getSubtotalExcludingTax();
        $actualPrices['subtotal_incl_tax'] = $this->checkoutCart->getTotalsBlock()->getSubtotalIncludingTax();
        $actualPrices['discount'] = $this->checkoutCart->getTotalsBlock()->getDiscount();
        $actualPrices['shipping_excl_tax'] = $this->checkoutCart->getTotalsBlock()->getShippingPrice();
        $actualPrices['shipping_incl_tax'] = $this->checkoutCart->getTotalsBlock()->getShippingPriceInclTax();
        $actualPrices['tax'] = $this->checkoutCart->getTotalsBlock()->getTax();
        $actualPrices['grand_total_excl_tax'] =
            $this->checkoutCart->getTotalsBlock()->getGrandTotalExcludingTax();
        $actualPrices['grand_total_incl_tax'] =
            $this->checkoutCart->getTotalsBlock()->getGrandTotalIncludingTax();
        return $actualPrices;
    }

    /**
     * Text of Tax Rule is applied
     *
     * @return string
     */
    public function toString()
    {
        return 'Prices on front is correct';
    }
}
