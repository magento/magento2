<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\CatalogSearch\Test\Page\CatalogsearchResult;

/**
 * Assert that search query length truncated to 103 symbols.
 */
class AssertCatalogSearchQueryLength extends AbstractConstraint
{
    /**
     * Assert that search query length truncated to 103 symbols.
     *
     * @param CatalogsearchResult $catalogSearchResult
     * @return void
     */
    public function processAssert(CatalogsearchResult $catalogSearchResult)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            $catalogSearchResult->getSearchResultsTitleBlock()->searchQueryLength(),
            103,
            'Search query length is not truncated to 103 symbols.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Search query truncated to 103 symbols.';
    }
}
