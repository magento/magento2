<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Constraint;

use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Tax\Test\Constraint\AbstractAssertTaxRuleIsAppliedToAllPrices;

/**
 * Checks that prices excl tax on category, product and cart pages are equal to specified in dataset.
 */
abstract class AbstractAssertTaxRuleIsAppliedToAllPricesOnGroupedProductPage extends
 AbstractAssertTaxRuleIsAppliedToAllPrices
{
    /**
     * Get grouped product view prices.
     *
     * @param FixtureInterface $product
     * @param array $actualPrices
     * @return array
     */
    abstract protected function getGroupedProductPagePrices(FixtureInterface $product, array $actualPrices);

    /**
     * Assert that specified prices are actual on category, product and cart pages.
     *
     * @param InjectableFixture $product
     * @param array $prices
     * @param int $qty
     * @param CmsIndex $cmsIndex
     * @param CatalogCategoryView $catalogCategoryView
     * @param CatalogProductView $catalogProductView
     * @param CheckoutCart $checkoutCart
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function processAssert(
        InjectableFixture $product,
        array $prices,
        $qty,
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
        $address = $fixtureFactory->createByCode('address', ['dataset' => 'US_address_NY']);
        $shipping = ['shipping_service' => 'Flat Rate', 'shipping_method' => 'Fixed'];
        $actualPrices = [];
        //Assertion steps
        $productCategory = $product->getCategoryIds()[0];
        $this->openCategory($productCategory);
        $actualPrices = $this->getCategoryPrices($product, $actualPrices);
        $catalogCategoryView->getListProductBlock()->getProductItem($product)->open();
        $catalogProductView->getGroupedProductViewBlock()->fillOptions($product);
        $actualPrices = $this->getGroupedProductPagePrices($product, $actualPrices);
        $catalogProductView->getGroupedProductViewBlock()->setQtyAndClickAddToCartGrouped($product, $qty);
        $catalogProductView->getMessagesBlock()->waitSuccessMessage();
        $this->checkoutCart->open();
        $this->fillEstimateBlock($address, $shipping);
        $actualPrices = $this->getCartPrices($product, $actualPrices);
        $actualPrices = $this->getTotals($actualPrices);
        //Prices verification
        $message = 'Prices from dataset should be equal to prices on frontend.';
        \PHPUnit_Framework_Assert::assertEquals($prices, $actualPrices, $message);
    }
}
