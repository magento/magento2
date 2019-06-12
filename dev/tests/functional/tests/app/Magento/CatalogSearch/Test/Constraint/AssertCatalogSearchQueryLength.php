<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\CatalogSearch\Test\Page\CatalogsearchResult;

/**
 * Assert that search query length truncated to 128 symbols.
 */
class AssertCatalogSearchQueryLength extends AbstractConstraint
{
    /**
     * Assert that search query length truncated to 128 symbols.
     *
     * @param CatalogsearchResult $catalogSearchResult
     * @return void
     */
    public function processAssert(CatalogsearchResult $catalogSearchResult)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            mb_strlen($catalogSearchResult->getSearchResultsTitleBlock()->getSearchQuery()),
            128,
            'Search query length is not truncated to 128 symbols.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Search query truncated to 128 symbols.';
    }
}
