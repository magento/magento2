<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertAttributeSetSuccessDeleteMessage
 * Check Attribute Sets success delete message
 */
class AssertAttributeSetSuccessDeleteMessage extends AbstractConstraint
{
    /**
     * Text value to be checked
     */
    const SUCCESS_DELETE_MESSAGE = 'The attribute set has been removed.';

    /**
     * Assert that after deleting attribute set success delete message appears
     *
     * @param CatalogProductSetIndex $productSetIndex
     * @return void
     */
    public function processAssert(CatalogProductSetIndex $productSetIndex)
    {
        $actualMessage = $productSetIndex->getMessagesBlock()->getSuccessMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_DELETE_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_DELETE_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Attribute Sets success delete message is present.';
    }
}
