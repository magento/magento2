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

use Mtf\ObjectManager;
use Mtf\Page\BackendPage;
use Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Fixture\OrderInjectable;

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
        $this->salesReportPage->getMessagesBlock()->clickLinkInMessages('notice', 'here');
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
            $salesItems += $product->getCheckoutData()['options']['qty'];
        }
        $expectedSalesData['orders'] += 1;
        $expectedSalesData['sales-items'] += $salesItems;
        $expectedSalesData['sales-total'] += $salesTotal;
        $expectedSalesData['invoiced'] += $invoice;
        return $expectedSalesData;
    }
}
