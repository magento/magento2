<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Mtf\Constraint\AbstractConstraint;
use Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery;
use Magento\CatalogSearch\Test\Page\Adminhtml\CatalogSearchIndex;

/**
 * Class AssertSearchTermInGrid
 * Assert that after save a term search on edit term search page displays
 */
class AssertSearchTermInGrid extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'high';

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
            'display_in_terms' => strtolower($searchTerm->getDisplayInTerms())
        ];

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
