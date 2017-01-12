<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Reports\Test\Page\Adminhtml\SalesShippingReport;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\System\Event\EventManagerInterface;

/**
 * Abstract assert for search in shipping report grid.
 */
abstract class AbstractAssertShippingReportResult extends AbstractConstraint
{
    /**
     * Shipping report page.
     *
     * @var SalesShippingReport
     */
    protected $salesShippingReport;

    /**
     * Order fixture.
     *
     * @var OrderInjectable
     */
    protected $order;

    /**
     * @constructor
     * @param ObjectManager $objectManager
     * @param EventManagerInterface $eventManager
     * @param SalesShippingReport $salesShippingReport
     */
    public function __construct(
        ObjectManager $objectManager,
        EventManagerInterface $eventManager,
        SalesShippingReport $salesShippingReport
    ) {
        parent::__construct($objectManager, $eventManager);
        $this->salesShippingReport = $salesShippingReport;
    }

    /**
     * Search in invoice report grid.
     *
     * @param array $shippingReport
     * @return void
     */
    protected function searchInShippingReportGrid(array $shippingReport)
    {
        $this->salesShippingReport->open();
        $this->salesShippingReport->getMessagesBlock()->clickLinkInMessage('notice', 'here');
        $this->salesShippingReport->getFilterForm()->viewsReport($shippingReport);
        $this->salesShippingReport->getActionBlock()->showReport();
    }

    /**
     * Prepare expected and initial results.
     *
     * @param array $expectedShippingData
     * @param array $shipmentResult
     * @return array
     */
    protected function prepareExpectedResult(array $expectedShippingData, array $shipmentResult)
    {
        $totalShipping = $this->order->getPrice()[0]['grand_shipment_total'];
        $expectedShippingData['qty'] += 1;
        $expectedShippingData['total-sales-shipping'] += $totalShipping;

        $preparedResult = [$expectedShippingData, $shipmentResult];
        foreach ($preparedResult as &$result) {
            $result = array_map(function ($rowData) {
                return (int)$rowData;
            }, $result);
        }
        return $preparedResult;
    }
}
