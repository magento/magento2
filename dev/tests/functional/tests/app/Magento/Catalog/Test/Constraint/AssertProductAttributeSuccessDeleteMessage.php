<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertSuccessDeletedAttribute
 * Check success message on Attribute page
 */
class AssertProductAttributeSuccessDeleteMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    const SUCCESS_MESSAGE = 'The product attribute has been deleted.';

    /**
     * Assert that message "The product attribute has been deleted." is present on Attribute page
     *
     * @param CatalogProductAttributeIndex $attributeIndex
     * @return void
     */
    public function processAssert(CatalogProductAttributeIndex $attributeIndex)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $attributeIndex->getMessagesBlock()->getSuccessMessages(),
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_MESSAGE
            . "\nActual: " . $attributeIndex->getMessagesBlock()->getSuccessMessages()
        );
    }

    /**
     * Text success present delete message
     *
     * @return string
     */
    public function toString()
    {
        return 'Attribute success delete message is present.';
    }
}
