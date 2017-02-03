<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery;
use Magento\CatalogSearch\Test\Page\Adminhtml\CatalogSearchIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertSearchTermInGrid
 * Assert that after save a term search on edit term search page displays
 */
class AssertSearchTermInGrid extends AbstractConstraint
{
    /**
     * Assert that after save a term search on edit term search page displays:
     *  - correct Search Query field passed from fixture
     *  - correct Store
     *  - correct Results
     *  - correct Uses
     *  - correct Synonym
     *  - correct Redirect URL
     *  - correct Suggested Terms
     *
     * @param CatalogSearchIndex $indexPage
     * @param CatalogSearchQuery $searchTerm
     * @return void
     */
    public function processAssert(CatalogSearchIndex $indexPage, CatalogSearchQuery $searchTerm)
    {
        $grid = $indexPage->open()->getGrid();
        $filters = [
            'search_query' => $searchTerm->getQueryText(),
            'store_id' => $searchTerm->getStoreId(),
            'results_from' => $searchTerm->getNumResults(),
            'popularity_from' => $searchTerm->getPopularity(),
            'synonym_for' => $searchTerm->getSynonymFor(),
            'redirect' => $searchTerm->getRedirect(),
            'display_in_terms' => strtolower($searchTerm->getDisplayInTerms()),
        ];

        $filters = array_filter($filters);
        $grid->search($filters);
        unset($filters['store_id']);
        \PHPUnit_Framework_Assert::assertTrue(
            $grid->isRowVisible($filters, false),
            'Row terms according to the filters is not found.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Row term according to the filters is not found.';
    }
}
