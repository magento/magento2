<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\TestCase;

use Magento\Reports\Test\Page\Adminhtml\SalesShippingReport;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Go to Reports > Sales > Shipping.
 * 2. Refresh statistic.
 * 3. Configure and apply filter.
 * 4. Remember report result.
 * 5. Place order.
 * 6. Create Shipping.
 * 7. Refresh statistic.
 *
 * Steps:
 * 1. Go to Reports > Sales > Shipping.
 * 2. Configure and apply filter.
 * 3. Perform all asserts.
 *
 * @ZephyrId MAGETWO-40914
 */
class SalesShippingReportEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Create shipment.
     *
     * @param SalesShippingReport $salesShippingReport
     * @param OrderInjectable $order
     * @param array shippingReport
     * @return array
     */
    public function test(SalesShippingReport $salesShippingReport, OrderInjectable $order, array $shippingReport)
    {
        // Preconditions
        $salesShippingReport->open();
        $salesShippingReport->getMessagesBlock()->clickLinkInMessage('notice', 'here');
        $salesShippingReport->getFilterForm()->viewsReport($shippingReport);
        $salesShippingReport->getActionBlock()->showReport();
        $initialShippingResult = $salesShippingReport->getGridBlock()->getLastResult();
        $initialShippingTotalResult = $salesShippingReport->getGridBlock()->getTotalResult();
        $order->persist();
        $this->objectManager->create(
            \Magento\Sales\Test\TestStep\CreateShipmentStep::class,
            ['order' => $order]
        )->run();

        return [
            'initialShippingResult' => $initialShippingResult,
            'initialShippingTotalResult' => $initialShippingTotalResult,
        ];
    }
}
