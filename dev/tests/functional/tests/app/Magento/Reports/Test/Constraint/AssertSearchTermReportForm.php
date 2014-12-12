<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Reports\Test\Constraint;

use Magento\CatalogSearch\Test\Page\Adminhtml\CatalogSearchEdit;
use Magento\Reports\Test\Page\Adminhtml\SearchIndex;
use Mtf\Constraint\AbstractAssertForm;

/**
 * Class AssertSearchTermReportForm
 * Assert that Search Term Report form data equals to passed from dataSet
 */
class AssertSearchTermReportForm extends AbstractAssertForm
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that Search Term Report form data equals to passed from dataSet
     *
     * @param CatalogSearchEdit $catalogSearchEdit
     * @param SearchIndex $searchIndex
     * @param string $productName
     * @param int $countProducts
     * @param int $countSearch
     * @return void
     */
    public function processAssert(
        CatalogSearchEdit $catalogSearchEdit,
        SearchIndex $searchIndex,
        $productName,
        $countProducts,
        $countSearch
    ) {
        $filter = [
            'query_text' => $productName,
            'num_results' => $countProducts,
            'popularity' => $countSearch,
        ];
        $searchIndex->open();
        $searchIndex->getSearchGrid()->searchAndOpen($filter);

        $dataDiff = $this->verifyData($filter, $catalogSearchEdit->getForm()->getData());

        \PHPUnit_Framework_Assert::assertEmpty($dataDiff, $dataDiff);
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Search Term Report form data equals to passed from dataSet.';
    }
}
