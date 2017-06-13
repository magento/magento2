<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;

/**
 * Class AssertUrlDuplicateErrorMessage
 */
class AssertUrlDuplicateErrorMessage extends AbstractConstraint
{
    /**
     * Text title of the error message to be checked.
     */
    const ERROR_MESSAGE_TITLE = 'The value specified in the URL Key field would generate a URL that already exists.';

    /**
     * Assert that success message is displayed after product save.
     *
     * @param CatalogProductEdit $productPage
     * @return void
     */
    public function processAssert(
        CatalogProductEdit $productPage,
        CatalogProductSimple $product,
        Category $category
    ) {
        $actualMessage = $productPage->getMessagesBlock()->getErrorMessage();
        \PHPUnit_Framework_Assert::assertContains(
            self::ERROR_MESSAGE_TITLE,
            $actualMessage,
            'Wrong error message is displayed.'
            . "\nExpected: " . self::ERROR_MESSAGE_TITLE
            . "\nActual:\n" . $actualMessage
        );

        \PHPUnit_Framework_Assert::assertContains(
            $product->getUrlKey(),
            $actualMessage,
            'Product url is not present on error message.'
            . "\nExpected: " . self::ERROR_MESSAGE_TITLE
            . "\nActual:\n" . $actualMessage
        );

        \PHPUnit_Framework_Assert::assertContains(
            $category->getUrlKey() . '/' . $product->getUrlKey(),
            $actualMessage,
            'Category url is not present on error message.'
            . "\nExpected: " . self::ERROR_MESSAGE_TITLE
            . "\nActual:\n" . $actualMessage
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Product url duplication error on save message is present.';
    }
}
