<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;

/**
 * Check mass update success message.
 */
class AssertMassProductUpdateSuccessMessage extends AbstractConstraint
{
    /**
     * Text value to be checked.
     */
    const SUCCESS_MESSAGE = 'A total of %s record(s) were updated.';

    /**
     * Assert that after mass update successful message appears.
     *
     * @param CatalogProductIndex $productGrid
     * @param int $productsCount
     * @return void
     */
    public function processAssert(CatalogProductIndex $productGrid, $productsCount)
    {
        $expectedMessage = sprintf(self::SUCCESS_MESSAGE, $productsCount);
        $actualMessage = $productGrid->getMessagesBlock()->getSuccessMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            $expectedMessage,
            $actualMessage,
            'Wrong success message is displayed.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Mass update success message is present.';
    }
}
