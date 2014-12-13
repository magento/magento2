<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
    const SUCCESS_MESSAGE = 'The product attribute has been deleted.';

    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'high';

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
