<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryEdit;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCategorySuccessDeleteMessage
 * Assert that after delete a category "You deleted the category." successful message appears
 */
class AssertCategorySuccessDeleteMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Message that displayed after delete url rewrite
     */
    const SUCCESS_DELETE_MESSAGE = 'You deleted the category.';

    /**
     * Assert that after delete a category "You deleted the category." successful message appears
     *
     * @param CatalogCategoryEdit $categoryEdit
     * @return void
     */
    public function processAssert(CatalogCategoryEdit $categoryEdit)
    {
        $actualMessage = $categoryEdit->getMessagesBlock()->getSuccessMessages();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_DELETE_MESSAGE,
            $actualMessage,
            'Wrong success delete message is displayed.'
            . "\nExpected: " . self::SUCCESS_DELETE_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Category delete message is displayed
     *
     * @return string
     */
    public function toString()
    {
        return 'Category delete message is displayed.';
    }
}
