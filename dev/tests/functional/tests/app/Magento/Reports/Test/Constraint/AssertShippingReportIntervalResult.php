<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Sales\Test\Fixture\OrderInjectable;

/**
 * Assert that shipment info in report grid is actual.
 */
class AssertShippingReportIntervalResult extends AbstractAssertShippingReportResult
{
    /**
     * Assert that shipment info in report grid is actual.
     *
     * @param OrderInjectable $order
     * @param array $shippingReport
     * @param array $initialShippingResult
     * @return void
     */
    public function processAssert(OrderInjectable $order, array $shippingReport, array $initialShippingResult)
    {
        $this->order = $order;
        $this->searchInShippingReportGrid($shippingReport);
        $shipmentResult = $this->salesShippingReport->getGridBlock()->getLastResult();
        $prepareInitialResults = $this->prepareExpectedResult($initialShippingResult, $shipmentResult);
        list($prepareInitialResult, $shipmentResult) = $prepareInitialResults;
        \PHPUnit\Framework\Assert::assertEquals(
            $prepareInitialResult,
            $shipmentResult,
            "Shipment report interval result not contains actual data."
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Shipment report interval result contains actual data.';
    }
}
