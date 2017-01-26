<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Checkout\Test\Fixture\Cart;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;

class AssertVisibleItemsQtyInCheckoutSummaryBlock extends AbstractConstraint
{
    /**
     * Assert that visible cart items count are the same as configuration value
     *
     * @param CmsIndex $cmsIndex
     * @param CheckoutOnepage $checkoutPage
     * @param Cart $cart
     * @param int $checkoutSummaryMaxVisibleCartItemsCount
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        CheckoutOnepage $checkoutPage,
        Cart $cart,
        $checkoutSummaryMaxVisibleCartItemsCount
    ) {
        $sidebar = $cmsIndex->getCartSidebarBlock();
        $sidebar->clickProceedToCheckoutButton();

        $reviewBlock = $checkoutPage->getReviewBlock();
        $reviewBlock->expandItemsBlock();

        $sourceProducts = $cart->getDataFieldConfig('items')['source'];
        $products = $sourceProducts->getProducts();

        $presentItems = 0;
        foreach (array_keys($cart->getItems()) as $key) {
            /** @var CatalogProductSimple $product */
            $product = $products[$key];
            if ($reviewBlock->getItemElement($product->getName())->isVisible()) {
                $presentItems++;
            }
        }

        \PHPUnit_Framework_Assert::assertEquals(
            $checkoutSummaryMaxVisibleCartItemsCount,
            $presentItems,
            'Wrong quantity of visible Cart items in checkout summary block.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Quantity of visible Cart items the same as checkout configuration value.';
    }
}
