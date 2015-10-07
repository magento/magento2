<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Customer\Test\Fixture\Address;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Checks that prices excl tax on category, product and cart pages are equal to specified in dataset.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractAssertTaxRuleIsAppliedToAllPrices extends AbstractConstraint
{
    /**
     * Cms index page.
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Catalog product page.
     *
     * @var catalogCategoryView
     */
    protected $catalogCategoryView;

    /**
     * Catalog product page.
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Catalog product page.
     *
     * @var CheckoutCart
     */
    protected $checkoutCart;

    /**
     * Constraint severeness.
     *
     * @var string
     */
    protected $severeness = 'high';

    /**
     * Implementation for get category prices function
     *
     * @param FixtureInterface $product
     * @param array $actualPrices
     * @return array
     */
    abstract protected function getCategoryPrices(FixtureInterface $product, $actualPrices);

    /**
     * Implementation for get product page prices function
     *
     * @param array $actualPrices
     * @return array
     */
    abstract protected function getProductPagePrices($actualPrices);

    /**
     * Implementation for get totals in cart function
     *
     * @param array $actualPrices
     * @return array
     */
    abstract protected function getTotals($actualPrices);

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
        $catalogProductView->getViewBlock()->fillOptions($product);
        $actualPrices = $this->getProductPagePrices($actualPrices);
        $catalogProductView->getViewBlock()->setQtyAndClickAddToCart($qty);
        $catalogProductView->getMessagesBlock()->waitSuccessMessage();
        $this->checkoutCart->open();
        $this->fillEstimateBlock($address, $shipping);
        $actualPrices = $this->getCartPrices($product, $actualPrices);
        $actualPrices = $this->getTotals($actualPrices);
        //Prices verification
        $message = 'Prices from dataset should be equal to prices on frontend.';
        \PHPUnit_Framework_Assert::assertEquals($prices, $actualPrices, $message);
    }

    /**
     * Open product category.
     *
     * @param string $productCategory
     * @return void
     */
    public function openCategory($productCategory)
    {
        $this->cmsIndex->open();
        $this->cmsIndex->getTopmenu()->selectCategoryByName($productCategory);
    }


    /**
     * Get cart prices.
     *
     * @param InjectableFixture $product
     * @param $actualPrices
     * @return array
     */
    public function getCartPrices(InjectableFixture $product, $actualPrices)
    {
        $actualPrices['cart_item_price_excl_tax'] =
            $this->checkoutCart->getCartBlock()->getCartItem($product)->getPriceExclTax();
        $actualPrices['cart_item_price_incl_tax'] =
            $this->checkoutCart->getCartBlock()->getCartItem($product)->getPriceInclTax();
        $actualPrices['cart_item_subtotal_excl_tax'] =
            $this->checkoutCart->getCartBlock()->getCartItem($product)->getSubtotalPriceExclTax();
        $actualPrices['cart_item_subtotal_incl_tax'] =
            $this->checkoutCart->getCartBlock()->getCartItem($product)->getSubtotalPriceInclTax();

        return $actualPrices;
    }

    /**
     * Fill estimate block.
     *
     * @param Address $address
     * @param array $shipping
     * @return void
     */
    public function fillEstimateBlock(Address $address, $shipping)
    {
        $this->checkoutCart->getShippingBlock()->fillEstimateShippingAndTax($address);
        $this->checkoutCart->getShippingBlock()->selectShippingMethod($shipping);
    }

    /**
     * Text of Tax Rule is applied
     *
     * @return string
     */
    public function toString()
    {
        return 'Prices on front is correct.';
    }
}
