<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Reports\Test\Page\Adminhtml\SalesTaxReport;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Tax\Test\Fixture\TaxRule;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertTaxReportInGrid
 * Check that sales info in report: Tax, Rate, Orders, Tax Amount on tax report page
 */
class AssertTaxReportInGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert sales info in report: Tax, Rate, Orders, Tax Amount on tax report page
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

        \PHPUnit_Framework_Assert::assertTrue(
            $salesTaxReport->getGridBlock()->isRowVisible($filter, false),
            "Tax Report is not visible in grid on tax report page."
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return "Sales info in report: Tax, Rate, Orders, Tax Amount is correct in grid on tax report page.";
    }
}
