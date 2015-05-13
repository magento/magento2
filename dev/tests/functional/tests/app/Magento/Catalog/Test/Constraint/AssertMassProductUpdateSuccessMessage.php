<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
    const SUCCESS_MESSAGE = 'A total of 1 record(s) were updated.';

    /**
     * Assert that after mass update successful message appears.
     *
     * @param CatalogProductIndex $productGrid
     * @return void
     */
    public function processAssert(CatalogProductIndex $productGrid)
    {
        $actualMessage = $productGrid->getMessagesBlock()->getSuccessMessages();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
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
