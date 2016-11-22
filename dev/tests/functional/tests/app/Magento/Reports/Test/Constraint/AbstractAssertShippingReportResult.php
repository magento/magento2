<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * Constructor.
     *
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
     * Prepare expected result.
     *
     * @param array $expectedShippingData
     * @return array
     */
    protected function prepareExpectedResult(array $expectedShippingData)
    {
        $totalShipping = $this->order->getPrice()[0]['grand_shipment_total'];
        $expectedShippingData['qty'] += 1;
        $expectedShippingData['total-sales-shipping'] += $totalShipping;

        return $expectedShippingData;
    }
}
