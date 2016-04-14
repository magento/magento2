<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductCompare;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class AssertProductCompareSuccessRemoveMessage
 * Assert message is appeared on "Compare Products" block on myAccount page
 */
class AssertProductCompareSuccessRemoveMessage extends AbstractConstraint
{
    const SUCCESS_MESSAGE = 'You removed product %s from the comparison list.';

    /**
     * Assert message is appeared on "Compare Products" block on myAccount page
     *
     * @param CatalogProductCompare $catalogProductCompare
     * @param FixtureInterface $product
     * @return void
     */
    public function processAssert(CatalogProductCompare $catalogProductCompare, FixtureInterface $product)
    {
        $successMessage = sprintf(self::SUCCESS_MESSAGE, $product->getName());
        $actualMessage = $catalogProductCompare->getMessagesBlock()->getSuccessMessage();
        \PHPUnit_Framework_Assert::assertEquals($successMessage, $actualMessage, 'Wrong success message is displayed.');
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Product has been removed from compare products list.';
    }
}
