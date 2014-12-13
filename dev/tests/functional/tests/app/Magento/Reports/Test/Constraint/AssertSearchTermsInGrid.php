<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Reports\Test\Page\Adminhtml\SearchIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertSearchTermsInGrid
 * Assert that Search Terms report in Search Terms grid
 */
class AssertSearchTermsInGrid extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that Search Terms report in grid
     *
     * @param SearchIndex $searchIndex
     * @param string $productName
     * @param int $countProducts
     * @param int $countSearch
     * @return void
     */
    public function processAssert(SearchIndex $searchIndex, $productName, $countProducts, $countSearch)
    {
        $filter = [
            'query_text' => $productName,
            'num_results' => $countProducts,
            'popularity' => $countSearch,
        ];
        \PHPUnit_Framework_Assert::assertTrue(
            $searchIndex->getSearchGrid()->isRowVisible($filter),
            'Search terms report is absent in Search Terms grid.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Search terms report is present in Search Terms grid.';
    }
}
