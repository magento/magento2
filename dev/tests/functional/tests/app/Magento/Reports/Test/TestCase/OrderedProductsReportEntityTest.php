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

use Magento\Reports\Test\Page\Adminhtml\OrderedProductsReport;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for OrderedProductsReportEntity
 *
 * Test Flow:
 * Preconditions:
 * 1. Place order
 *
 * Steps:
 * 1. Open Backend
 * 2. Go to Reports > Products > Ordered
 * 3. Select time range and report period
 * 4. Click "Refresh button"
 * 5. Perform all assertions
 *
 * @group Reports_(MX)
 * @ZephyrId MAGETWO-28200
 */
class OrderedProductsReportEntityTest extends Injectable
{
    /**
     * Ordered Products Report
     *
     * @var OrderedProductsReport
     */
    protected $orderedProducts;

    /**
     * Inject pages
     *
     * @param OrderedProductsReport $orderedProducts
     * @return void
     */
    public function __inject(OrderedProductsReport $orderedProducts)
    {
        $this->orderedProducts = $orderedProducts;
    }

    /**
     * Search order products report
     *
     * @param OrderInjectable $order
     * @param array $customersReport
     * @return void
     */
    public function test(OrderInjectable $order, array $customersReport)
    {
        // Preconditions
        $order->persist();

        // Steps
        $this->orderedProducts->open();
        $this->orderedProducts->getGridBlock()->searchAccounts($customersReport);
    }
}
