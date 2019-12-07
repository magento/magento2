<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
 * Assert that product price and qty in  mini shopping cart are equal to expected price from data set.
 */
class AssertProductDataInMiniShoppingCart extends AbstractAssertForm
{
    /**
     * Assert that product price and qty in mini shopping cart equal to expected price from data set.
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
            $cartItem = $cmsIndex->getCartSidebarBlock()->getCartItem($product);

            $productsData[$productName]['price'] = ['price' => $checkoutItem['price']];
            $miniCartData[$productName]['price'] = [
                'price' => $cartItem->getPrice()
            ];
            $productsData[$productName]['qty'] = [
                'qty' => $checkoutItem['qty'],
            ];
            $miniCartData[$productName]['qty'] = [
                'qty' => $cartItem->getQty(),
            ];
        }

        $error = $this->verifyData($productsData, $miniCartData, true);
        \PHPUnit\Framework\Assert::assertEmpty($error, $error);
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Price and qty in mini shopping cart equals to expected price from data set.';
    }
}
