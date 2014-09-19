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

namespace Magento\Reports\Test\TestCase;

use Mtf\TestCase\Injectable;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Reports\Test\Page\Adminhtml\Bestsellers;

/**
 * Test Creation for BestsellerProductsReportEntity
 *
 * Test Flow:
 * Preconditions:
 * 1. Create customer
 * 2. Create product
 * 3. Place order
 * 4. Refresh statistic
 *
 * Steps:
 * 1. Open Backend
 * 2. Go to Reports > Products > Bestsellers
 * 3. Select time range, report period
 * 4. Click "Show report"
 * 5. Perform all assertions
 *
 * @group Reports_(MX)
 * @ZephyrId MAGETWO-28222
 */
class BestsellerProductsReportEntityTest extends Injectable
{
    /**
     * Bestsellers page
     *
     * @var Bestsellers
     */
    protected $bestsellers;

    /**
     * Inject pages
     *
     * @param Bestsellers $bestsellers
     * @return void
     */
    public function __inject(Bestsellers $bestsellers)
    {
        $this->bestsellers = $bestsellers;
    }

    /**
     * Bestseller Products Report
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
        $this->bestsellers->getMessagesBlock()->clickLinkInMessages('notice', 'here');

        // Steps
        $this->bestsellers->getFilterBlock()->viewsReport($bestsellerReport);
        $this->bestsellers->getActionsBlock()->showReport();
    }
}
