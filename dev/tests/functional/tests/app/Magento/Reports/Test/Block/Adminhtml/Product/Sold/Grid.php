<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Block\Adminhtml\Product\Sold;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\ObjectManager;

/**
 * Class Grid
 * Ordered Products Report grid
 */
class Grid extends \Magento\Backend\Test\Block\Widget\Grid
{
    /**
     * Mapping for fields in Ordered Products Report Grid
     *
     * @var array
     */
    protected $dataMapping = [
        'report_from' => 'datepicker',
        'report_to' => 'datepicker',
        'report_period' => 'select',
    ];

    /**
     * Product in grid locator
     *
     * @var string
     */
    protected $product = './/*[contains(.,"%s")]/*[contains(@class,"col-qty")]';

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
                \Magento\Backend\Test\Fixture\Source\Date::class,
                ['params' => [], 'data' => ['pattern' => $reportFilter]]
            );
            $customersReport[$name] = $date->getData();
        }
        return $customersReport;
    }

    /**
     * Get orders quantity from Ordered Products Report grid
     *
     * @param OrderInjectable $order
     * @return array
     */
    public function getOrdersResults(OrderInjectable $order)
    {
        $products = $order->getEntityId()['products'];
        $views = [];
        foreach ($products as $key => $product) {
            $views[$key] = $this->_rootElement
                ->find(sprintf($this->product, $product->getName()), Locator::SELECTOR_XPATH)->getText();
        }
        return $views;
    }
}
