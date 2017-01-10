<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Page\BackendPage;

/**
 * Class AbstractAssertSalesReportResult
 * Abstract assert for search in sales report grid
 */
abstract class AbstractAssertSalesReportResult extends AbstractConstraint
{
    /**
     * Sales report page
     *
     * @var BackendPage
     */
    protected $salesReportPage;

    /**
     * Order
     *
     * @var OrderInjectable
     */
    protected $order;

    /**
     * Search in sales report grid
     *
     * @param array $salesReport
     * @return void
     */
    protected function searchInSalesReportGrid(array $salesReport)
    {
        $this->salesReportPage->open();
        $this->salesReportPage->getMessagesBlock()->clickLinkInMessage('notice', 'here');
        $this->salesReportPage->getFilterBlock()->viewsReport($salesReport);
        $this->salesReportPage->getActionBlock()->showReport();
    }

    /**
     * Prepare expected result
     *
     * @param array $expectedSalesData
     * @return array
     */
    protected function prepareExpectedResult(array $expectedSalesData)
    {
        $salesItems = 0;
        $invoice = $this->order->getPrice()[0]['grand_invoice_total'];
        $salesTotal = $this->order->getPrice()[0]['grand_order_total'];
        foreach ($this->order->getEntityId()['products'] as $product) {
            $salesItems += $product->getCheckoutData()['qty'];
        }
        $expectedSalesData['orders'] += 1;
        $expectedSalesData['sales-items'] += $salesItems;
        $expectedSalesData['sales-total'] += $salesTotal;
        $expectedSalesData['invoiced'] += $invoice;
        return $expectedSalesData;
    }
}
