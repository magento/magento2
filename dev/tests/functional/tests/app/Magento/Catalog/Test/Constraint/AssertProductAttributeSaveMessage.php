<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductAttributeSaveMessage
 */
class AssertProductAttributeSaveMessage extends AbstractConstraint
{
    /**
     * Product attribute success save message.
     */
    const SUCCESS_MESSAGE = 'You saved the product attribute.';

    /**
     * Assert that message "You saved the product attribute." is present on Attribute page
     *
     * @param CatalogProductAttributeIndex $attributeIndex
     * @return void
     */
    public function processAssert(CatalogProductAttributeIndex $attributeIndex)
    {
        $actualMessage = $attributeIndex->getMessagesBlock()->getSuccessMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Text success present save message
     *
     * @return string
     */
    public function toString()
    {
        return 'Attribute success save message is present.';
    }
}
