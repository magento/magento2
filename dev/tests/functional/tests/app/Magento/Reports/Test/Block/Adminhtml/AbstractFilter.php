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

namespace Magento\Reports\Test\Block\Adminhtml;

use Mtf\Block\Form;
use Mtf\ObjectManager;

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
                    '\Magento\Backend\Test\Fixture\Date',
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
