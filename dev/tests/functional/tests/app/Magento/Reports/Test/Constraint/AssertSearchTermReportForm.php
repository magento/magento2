<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Constraint;

use Magento\CatalogSearch\Test\Page\Adminhtml\CatalogSearchEdit;
use Magento\Reports\Test\Page\Adminhtml\SearchIndex;
use Magento\Mtf\Constraint\AbstractAssertForm;

/**
 * Class AssertSearchTermReportForm
 * Assert that Search Term Report form data equals to passed from dataset
 */
class AssertSearchTermReportForm extends AbstractAssertForm
{
    /**
     * Assert that Search Term Report form data equals to passed from dataset
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

        \PHPUnit\Framework\Assert::assertEmpty($dataDiff, $dataDiff);
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Search Term Report form data equals to passed from dataset.';
    }
}
