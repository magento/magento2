<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\CatalogSearch\Test\Page\CatalogsearchResult;

/**
 * Assert that product cannot be found via Quick Search.
 */
class AssertProductNotSearchable extends AbstractConstraint
{
    /**
     * Notice message about no results on search.
     */
    const NOTICE_MESSAGE = 'Your search returned no results.';

    /**
     * Assert that product cannot be found via Quick Search.
     *
     * @param CatalogsearchResult $catalogSearchResult
     * @return void
     */
    public function processAssert(CatalogsearchResult $catalogSearchResult)
    {
        $actualMessage = $catalogSearchResult->getMessagesBlock()->getNoticeMessages();

        \PHPUnit_Framework_Assert::assertEquals(
            self::NOTICE_MESSAGE,
            $actualMessage,
            'Wrong message is displayed or no message at all.'
            . "\nExpected: " . self::NOTICE_MESSAGE
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
        return "Product is not searchable.";
    }
}
