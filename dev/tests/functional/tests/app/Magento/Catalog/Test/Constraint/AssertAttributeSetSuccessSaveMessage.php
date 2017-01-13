<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertAttributeSetSuccessSaveMessage
 * Check Attribute Sets success save message
 */
class AssertAttributeSetSuccessSaveMessage extends AbstractConstraint
{
    /**
     * Text value to be checked
     */
    const SUCCESS_MESSAGE = 'You saved the attribute set.';

    /**
     * Assert that after save a attribute set "You saved the attribute set." successful message appears
     *
     * @param CatalogProductSetIndex $productSetIndex
     * @return void
     */
    public function processAssert(CatalogProductSetIndex $productSetIndex)
    {
        $actualMessage = $productSetIndex->getMessagesBlock()->getSuccessMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_MESSAGE
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
        return 'Attribute Sets success save message is present.';
    }
}
