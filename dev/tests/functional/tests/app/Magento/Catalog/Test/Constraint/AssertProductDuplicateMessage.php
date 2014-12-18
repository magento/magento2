<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductDuplicateMessage
 */
class AssertProductDuplicateMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    /**
     * Text value to be checked
     */
    const DUPLICATE_MESSAGE = 'You duplicated the product.';

    /**
     * Checking the output message successful product duplication
     *
     * @param CatalogProductEdit $productPage
     * @return void
     */
    public function processAssert(CatalogProductEdit $productPage)
    {
        $actualMessages = $productPage->getMessagesBlock()->getSuccessMessages();
        $actualMessages = is_array($actualMessages) ? $actualMessages : [$actualMessages];
        \PHPUnit_Framework_Assert::assertContains(
            self::DUPLICATE_MESSAGE,
            $actualMessages,
            'Wrong duplicated message is displayed.'
            . "\nExpected: " . self::DUPLICATE_MESSAGE
            . "\nActual:\n" . implode("\n - ", $actualMessages)
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Product duplicated message is present.';
    }
}
