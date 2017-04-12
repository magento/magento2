<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;

/**
 * Assert that Items Ordered section on Create Order page on backend contains products.
 */
class AssertItemsOrderedSectionContainsProducts extends AbstractConstraint
{
    /**
     * Assert that Items Ordered section on Create Order page on backend contains products.
     *
     * @param OrderCreateIndex $orderCreateIndex
     * @param array $products
     * @return void
     */
    public function processAssert(OrderCreateIndex $orderCreateIndex, array $products)
    {
        foreach ($products as $product) {
            $expectedItemNames[] = $product->getName();
        }
        $itemsNames = $orderCreateIndex->getCreateBlock()->getItemsBlock()->getItemsNames();
        \PHPUnit_Framework_Assert::assertEquals(
            sort($expectedItemNames),
            sort($itemsNames),
            "Items Ordered section on Create Order page on backend doesn't contain correct products."
        );
    }

    /**
     * Success assert message that Items Ordered section on Create Order page on backend contains products.
     *
     * @return string
     */
    public function toString()
    {
        return "Items Ordered section on Create Order page on backend contains correct products.";
    }
}
