<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertReviewCreationSuccessMessage
 */
class AssertReviewCreationSuccessMessage extends AbstractConstraint
{
    /**
     * Text of success message after review created
     */
    const SUCCESS_MESSAGE = 'You submitted your review for moderation.';

    /**
     * Assert that success message is displayed after review created
     *
     * @param CatalogProductView $catalogProductView
     * @return void
     */
    public function processAssert(CatalogProductView $catalogProductView)
    {
        $actualMessage = $catalogProductView->getMessagesBlock()->getSuccessMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Text success create message is displayed
     *
     * @return string
     */
    public function toString()
    {
        return 'Review success create message is present.';
    }
}
