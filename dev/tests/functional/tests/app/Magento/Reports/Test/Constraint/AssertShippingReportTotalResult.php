<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Sales\Test\Fixture\OrderInjectable;

/**
 * Assert that total shipping info in report grid is actual.
 */
class AssertShippingReportTotalResult extends AbstractAssertShippingReportResult
{
    /**
     * Assert that total shipping info in report grid is actual.
     *
     * @param OrderInjectable $order
     * @param array $shippingReport
     * @param array $initialShippingTotalResult
     * @return void
     */
    public function processAssert(OrderInjectable $order, array $shippingReport, array $initialShippingTotalResult)
    {
        $this->order = $order;
        $this->searchInShippingReportGrid($shippingReport);
        $shipmentResult = $this->salesShippingReport->getGridBlock()->getTotalResult();
        $prepareInitialResults = $this->prepareExpectedResult($initialShippingTotalResult, $shipmentResult);
        list($prepareInitialResult, $shipmentResult) = $prepareInitialResults;
        \PHPUnit\Framework\Assert::assertEquals(
            $prepareInitialResult,
            $shipmentResult,
            "Grand total Shipment result is not correct."
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Shipment report grand total result contains actual data.';
    }
}
