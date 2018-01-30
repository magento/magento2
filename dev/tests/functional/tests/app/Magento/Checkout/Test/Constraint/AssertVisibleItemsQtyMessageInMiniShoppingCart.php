<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that quantity of visible Cart items is the same as minicart configuration value.
 */
class AssertVisibleItemsQtyMessageInMiniShoppingCart extends AbstractConstraint
{
    /**
     * Items counter default message.
     */
    const ITEMS_COUNTER_MASSAGE = "%s Items in Cart";

    /**
     * Items counter message with limitations.
     */
    const VISIBLE_ITEMS_COUNTER_MASSAGE = "%s of %s Items in Cart";

    /**
     * Assert that quantity of visible Cart items is the same as minicart configuration value.
     *
     * @param CmsIndex $cmsIndex
     * @param int $minicartMaxVisibleCartItemsCount
     * @param int $totalItemsCountInShoppingCart
     * @return void
     */
    public function processAssert(CmsIndex $cmsIndex, $minicartMaxVisibleCartItemsCount, $totalItemsCountInShoppingCart)
    {
        $sidebar = $cmsIndex->getCartSidebarBlock();
        $sidebar->openMiniCart();

        if ($totalItemsCountInShoppingCart > $minicartMaxVisibleCartItemsCount) {
            $counterMessage = sprintf(
                self::VISIBLE_ITEMS_COUNTER_MASSAGE,
                $minicartMaxVisibleCartItemsCount,
                $totalItemsCountInShoppingCart
            );
        } else {
            $counterMessage = sprintf(self::ITEMS_COUNTER_MASSAGE, $totalItemsCountInShoppingCart);
        }

        \PHPUnit_Framework_Assert::assertEquals(
            $counterMessage,
            $sidebar->getVisibleItemsCounter(),
            'Wrong quantity value of visible Cart items in mini shopping cart'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return 'Quantity of visible Cart items is the same as minicart configuration value.';
    }
}
