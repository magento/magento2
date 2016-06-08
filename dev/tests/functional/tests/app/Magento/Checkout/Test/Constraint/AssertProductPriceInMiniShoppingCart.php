<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Checkout\Test\Fixture\Cart;
use Magento\Checkout\Test\Fixture\Cart\Items;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractAssertForm;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert that product price in mini shopping cart equals to expected price from data set.
 */
class AssertProductPriceInMiniShoppingCart extends AbstractAssertForm
{
    /**
     * Assert that product price in  mini shopping cart is equal to expected price from data set.
     *
     * @param CmsIndex $cmsIndex
     * @param Cart $cart
     * @return void
     */
    public function processAssert(CmsIndex $cmsIndex, Cart $cart)
    {
        $cmsIndex->open();
        /** @var Items $sourceProducts */
        $sourceProducts = $cart->getDataFieldConfig('items')['source'];
        $products = $sourceProducts->getProducts();
        $items = $cart->getItems();
        $productsData = [];
        $miniCartData = [];

        foreach ($items as $key => $item) {
            /** @var CatalogProductSimple $product */
            $product = $products[$key];
            $productName = $product->getName();
            /** @var FixtureInterface $item */
            $checkoutItem = $item->getData();

            $productsData[$productName] = ['price' => $checkoutItem['price']];
            $miniCartData[$productName] = [
                'price' => $cmsIndex->getCartSidebarBlock()->getProductPrice($productName)
            ];
        }

        $error = $this->verifyData($productsData, $miniCartData, true);
        \PHPUnit_Framework_Assert::assertEmpty($error, $error);
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Price in mini shopping cart equals to expected price from data set.';
    }
}
