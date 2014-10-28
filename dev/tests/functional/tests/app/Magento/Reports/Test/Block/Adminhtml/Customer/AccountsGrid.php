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

namespace Magento\Reports\Test\Block\Adminhtml\Customer;

use Mtf\ObjectManager;
use Mtf\Client\Element\Locator;

/**
 * Class AccountsGrid
 * New Customer Account report grid
 */
class AccountsGrid extends \Magento\Backend\Test\Block\Widget\Grid
{
    /**
     * Mapping for fields in Account Report Grid
     *
     * @var array
     */
    protected $dataMapping = [
        'report_from' => 'datepicker',
        'report_to' => 'datepicker',
        'report_period' => 'select',
    ];

    /**
     * Total results locator
     *
     * @var string
     */
    protected $totalResults = 'tfoot .col-qty';

    /**
     * Filter locator
     *
     * @var string
     */
    protected $filter = '[name=%s]';

    /**
     * Refresh button locator
     *
     * @var string
     */
    protected $refreshButton = '[data-ui-id="adminhtml-report-grid-refresh-button"]';

    /**
     * Search accounts in report grid
     *
     * @var array $customersReport
     * @return void
     */
    public function searchAccounts(array $customersReport)
    {
        $customersReport = $this->prepareData($customersReport);
        foreach ($customersReport as $name => $value) {
            $this->_rootElement
                ->find(sprintf($this->filter, $name), Locator::SELECTOR_CSS, $this->dataMapping[$name])
                ->setValue($value);
        }
        $this->_rootElement->find($this->refreshButton)->click();
    }

    /**
     * Get total Results from New Accounts Report grid
     *
     * @return string
     */
    public function getTotalResults()
    {
        return $this->_rootElement->find($this->totalResults)->getText();
    }

    /**
     * Prepare data
     *
     * @param array $customersReport
     * @return array
     */
    protected function prepareData(array $customersReport)
    {
        foreach ($customersReport as $name => $reportFilter) {
            if ($name === 'report_period') {
                continue;
            }
            $date = ObjectManager::getInstance()->create(
                '\Magento\Backend\Test\Fixture\Date',
                ['params' => [], 'data' => ['pattern' => $reportFilter]]
            );
            $customersReport[$name] = $date->getData();
        }
        return $customersReport;
    }
}
