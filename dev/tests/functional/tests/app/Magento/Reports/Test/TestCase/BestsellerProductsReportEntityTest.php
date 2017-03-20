<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\TestCase;

use Magento\Reports\Test\Page\Adminhtml\Bestsellers;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Create customer.
 * 2. Create product.
 * 3. Place order.
 * 4. Refresh statistic.
 *
 * Steps:
 * 1. Open Backend.
 * 2. Go to Reports > Products > Bestsellers.
 * 3. Select time range, report period.
 * 4. Click "Show report".
 * 5. Perform all assertions.
 *
 * @group Reports
 * @ZephyrId MAGETWO-28222
 */
class BestsellerProductsReportEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Bestsellers page.
     *
     * @var Bestsellers
     */
    protected $bestsellers;

    /**
     * Inject pages.
     *
     * @param Bestsellers $bestsellers
     * @return void
     */
    public function __inject(Bestsellers $bestsellers)
    {
        $this->bestsellers = $bestsellers;
    }

    /**
     * Bestseller Products Report.
     *
     * @param OrderInjectable $order
     * @param array $bestsellerReport
     * @return void
     */
    public function test(OrderInjectable $order, array $bestsellerReport)
    {
        // Preconditions
        $order->persist();
        $this->bestsellers->open();
        $this->bestsellers->getMessagesBlock()->clickLinkInMessage('notice', 'here');

        // Steps
        $this->bestsellers->getFilterBlock()->viewsReport($bestsellerReport);
        $this->bestsellers->getActionsBlock()->showReport();
    }
}
