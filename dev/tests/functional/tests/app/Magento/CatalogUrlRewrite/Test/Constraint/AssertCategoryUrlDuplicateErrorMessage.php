<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryEdit;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\Category;

/**
 * Class AssertCategoryUrlDuplicateErrorMessage
 */
class AssertCategoryUrlDuplicateErrorMessage extends AbstractConstraint
{
    /**
     * Text title of the error message to be checked.
     */
    const ERROR_MESSAGE_TITLE = 'The value specified in the URL Key field would generate a URL that already exists.';

    /**
     * Assert that success message is displayed after category save.
     *
     * @param CatalogCategoryEdit $productPage
     * @param Category $category
     * @return void
     */
    public function processAssert(
        CatalogCategoryEdit $productPage,
        Category $category
    ) {
        $actualMessage = $productPage->getMessagesBlock()->getErrorMessage();
        \PHPUnit\Framework\Assert::assertContains(
            self::ERROR_MESSAGE_TITLE,
            $actualMessage,
            'Wrong error message is displayed.'
            . "\nExpected: " . self::ERROR_MESSAGE_TITLE
            . "\nActual:\n" . $actualMessage
        );

        \PHPUnit\Framework\Assert::assertContains(
            $category->getUrlKey(),
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
        return 'Category url duplication error on save message is present.';
    }
}
