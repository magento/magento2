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

namespace Magento\Reports\Test\Constraint;

use Mtf\Constraint\AbstractConstraint;
use Magento\Reports\Test\Page\Adminhtml\SearchIndex;

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
