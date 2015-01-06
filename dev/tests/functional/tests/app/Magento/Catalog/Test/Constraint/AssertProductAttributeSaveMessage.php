<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductAttributeSaveMessage
 */
class AssertProductAttributeSaveMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    const SUCCESS_MESSAGE = 'You saved the product attribute.';

    /**
     * Assert that message "You saved the product attribute." is present on Attribute page
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
     * Text success present save message
     *
     * @return string
     */
    public function toString()
    {
        return 'Attribute success save message is present.';
    }
}
