<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Block\Adminhtml\Sales\TaxRule;

use Magento\Reports\Test\Block\Adminhtml\AbstractFilter;

/**
 * Class Filter
 * Filter for Tax Rule Views Report
 */
class Filter extends AbstractFilter
{
    /**
     * Prepare data
     *
     * @param array $viewsReport
     * @return array
     */
    protected function prepareData(array $viewsReport)
    {
        return parent::prepareData(array_diff($viewsReport, ['-']));
    }
}
