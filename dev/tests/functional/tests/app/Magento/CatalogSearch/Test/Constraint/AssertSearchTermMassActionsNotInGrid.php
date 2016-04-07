<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery;
use Magento\CatalogSearch\Test\Page\Adminhtml\CatalogSearchIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertSearchTermMassActionsNotInGrid
 * Assert that after mass delete search terms on grid page are not displayed
 */
class AssertSearchTermMassActionsNotInGrid extends AbstractConstraint
{
    /**
     * Assert that after mass delete search terms on grid page are not displayed
     *
     * @param array $searchTerms
     * @param CatalogSearchIndex $indexPage
     * @param AssertSearchTermNotInGrid $assertSearchTermNotInGrid
     * @return void
     */
    public function processAssert(
        array $searchTerms,
        CatalogSearchIndex $indexPage,
        AssertSearchTermNotInGrid $assertSearchTermNotInGrid
    ) {
        foreach ($searchTerms as $term) {
            /** @var CatalogSearchQuery $term */
            $assertSearchTermNotInGrid->processAssert($indexPage, $term);
        }
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Search terms were not found in grid.';
    }
}
