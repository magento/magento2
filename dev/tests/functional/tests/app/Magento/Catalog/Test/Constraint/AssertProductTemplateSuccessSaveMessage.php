<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductTemplateSuccessSaveMessage
 * Check Product Templates success save message
 */
class AssertProductTemplateSuccessSaveMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    /**
     * Text value to be checked
     */
    const SUCCESS_MESSAGE = 'You saved the attribute set.';

    /**
     * Assert that after save a product template "You saved the attribute set." successful message appears
     *
     * @param CatalogProductSetIndex $productSetIndex
     * @return void
     */
    public function processAssert(CatalogProductSetIndex $productSetIndex)
    {
        $actualMessage = $productSetIndex->getMessagesBlock()->getSuccessMessages();
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
        return 'Product Templates success save message is present.';
    }
}
