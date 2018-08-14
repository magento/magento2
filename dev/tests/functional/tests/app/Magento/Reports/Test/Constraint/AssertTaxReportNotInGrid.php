<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Reports\Test\Page\Adminhtml\SalesTaxReport;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Tax\Test\Fixture\TaxRule;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertTaxReportNotInGrid
 * Check that Tax report is absent on tax report page
 */
class AssertTaxReportNotInGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert Tax report is absent on tax report page
     *
     * @param SalesTaxReport $salesTaxReport
     * @param OrderInjectable $order
     * @param TaxRule $taxRule
     * @param string $taxAmount
     * @return void
     */
    public function processAssert(
        SalesTaxReport $salesTaxReport,
        OrderInjectable $order,
        TaxRule $taxRule,
        $taxAmount
    ) {
        $filter = [
            'tax' => $taxRule->getTaxRate()[0],
            'rate' => $taxRule->getDataFieldConfig('tax_rate')['source']->getFixture()[0]->getRate(),
            'orders' => count($order->getEntityId()['products']),
            'tax_amount' => $taxAmount,
        ];

        \PHPUnit_Framework_Assert::assertFalse(
            $salesTaxReport->getGridBlock()->isRowVisible($filter, false),
            "Tax Report is visible in grid on tax report page."
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return "Sales info in report: Tax, Rate, Orders, Tax Amount is incorrect in grid on tax report page.";
    }
}
