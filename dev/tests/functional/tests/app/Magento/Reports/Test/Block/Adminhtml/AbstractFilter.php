<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Block\Adminhtml;

use Magento\Mtf\Block\Form;
use Magento\Mtf\ObjectManager;

/**
 * Abstract Class Filter
 * Filter for Report
 */
abstract class AbstractFilter extends Form
{
    /**
     * Date fields
     *
     * @var array
     */
    protected $dateFields = ['from', 'to'];

    /**
     * Refresh button css selector
     *
     * @var string
     */
    protected $refresh = '[data-ui-id="adminhtml-report-grid-refresh-button"]';

    /**
     * Prepare data
     *
     * @param array $viewsReport
     * @return array
     */
    protected function prepareData(array $viewsReport)
    {
        foreach ($viewsReport as $key => $reportFilter) {
            if (in_array($key, $this->dateFields)) {
                $date = ObjectManager::getInstance()->create(
                    \Magento\Backend\Test\Fixture\Source\Date::class,
                    ['params' => [], 'data' => ['pattern' => $reportFilter]]
                );
                $viewsReport[$key] = $date->getData();
            }
        }
        return $viewsReport;
    }

    /**
     * Search entity in report grid
     *
     * @var array $report
     * @return void
     */
    public function viewsReport(array $report)
    {
        $report = $this->prepareData($report);
        $data = $this->dataMapping($report);
        $this->_fill($data);
    }

    /**
     * Click refresh filter button
     *
     * @return void
     */
    public function refreshFilter()
    {
        $this->_rootElement->find($this->refresh)->click();
    }
}
