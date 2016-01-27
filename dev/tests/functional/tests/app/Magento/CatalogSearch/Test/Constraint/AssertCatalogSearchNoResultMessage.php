<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\CatalogSearch\Test\Page\CatalogsearchResult;

/**
 * Assert that notice message is visible.
 */
class AssertCatalogSearchNoResultMessage extends AbstractConstraint
{
    /**
     * Notice message about no results on search.
     */
    const NOTICE_MESSAGE = 'Your search returned no results.';

    /**
     * Assert that 'Your search returned no results.' is visible.
     *
     * @param CatalogsearchResult $catalogSearchResult
     * @return void
     */
    public function processAssert(CatalogsearchResult $catalogSearchResult)
    {
        \PHPUnit_Framework_Assert::assertTrue(
            $catalogSearchResult->getSearchResultBlock()->isVisibleMessages(self::NOTICE_MESSAGE),
            'Wrong message is displayed or no message at all.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Notice message is visible.';
    }
}
