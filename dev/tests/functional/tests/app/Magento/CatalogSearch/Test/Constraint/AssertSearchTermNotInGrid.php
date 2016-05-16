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
 * Class AssertSearchTermNotInGrid
 * Assert that after delete a search term on grid page not displayed
 */
class AssertSearchTermNotInGrid extends AbstractConstraint
{
    /**
     * Assert that after delete a search term on grid page not displayed
     *
     * @param CatalogSearchIndex $indexPage
     * @param CatalogSearchQuery $searchTerm
     * @return void
     */
    public function processAssert(CatalogSearchIndex $indexPage, CatalogSearchQuery $searchTerm)
    {
        $queryText = $searchTerm->getQueryText();
        $grid = $indexPage->open()->getGrid();
        $filters = [
            'search_query' => $queryText,
            'store_id' => $searchTerm->getStoreId(),
            'results_from' => $searchTerm->getNumResults(),
            'popularity_from' => $searchTerm->getPopularity(),
            'redirect' => $searchTerm->getRedirect(),
            'display_in_terms' => strtolower($searchTerm->getDisplayInTerms()),
        ];

        $grid->search($filters);
        unset($filters['store_id']);
        \PHPUnit_Framework_Assert::assertFalse(
            $grid->isRowVisible($filters, false),
            'Search term "' . $queryText . '" was found in grid.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Search term was not found in grid.';
    }
}
