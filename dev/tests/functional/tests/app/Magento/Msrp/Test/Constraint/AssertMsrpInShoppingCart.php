<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Msrp\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Checkout\Test\Page\CheckoutCart;

/**
 * Assert product MAP related data in Shopping Cart.
 */
class AssertMsrpInShoppingCart extends AbstractConstraint
{
    /**
     * Assert product MAP related data in Shopping Cart.
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogCategoryView $catalogCategoryView
     * @param CatalogProductView $catalogProductView
     * @param CheckoutCart $checkoutCart
     * @param InjectableFixture $product
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        CatalogCategoryView $catalogCategoryView,
        CatalogProductView $catalogProductView,
        CheckoutCart $checkoutCart,
        InjectableFixture $product
    ) {
        /** @var CatalogProductSimple $product */
        $cmsIndex->open();
        $cmsIndex->getTopmenu()->selectCategoryByName($product->getCategoryIds()[0]);
        $catalogCategoryView->getListProductBlock()->getProductItem($product)->open();

        if ($product->hasData('checkout_data') || $product->getMsrpDisplayActualPriceType() === 'In Cart') {
            $catalogProductView->getViewBlock()->addToCart($product);
        } else {
            $catalogProductView->getMsrpViewBlock()->openMapBlock();
            $catalogProductView->getMsrpViewBlock()->getMapBlock()->addToCart();
        }
        $catalogProductView->getMessagesBlock()->waitSuccessMessage();

        $checkoutCart->open();

        $priceData = $product->getDataFieldConfig('price')['source']->getPriceData();
        $productPrice = isset($priceData['category_price']) ? $priceData['category_price'] : $product->getPrice();
        $unitPrice = $checkoutCart->getCartBlock()->getCartItem($product)->getPrice();
        \PHPUnit\Framework\Assert::assertEquals($productPrice, $unitPrice, 'Incorrect unit price is displayed in Cart');
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return "Displayed Product MAP data in Shopping Cart is correct.";
    }
}
