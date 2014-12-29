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

namespace Magento\Backend\Test\Constraint;

use Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;

/**
 * Assert that bestsellers tab content on Dashboard successfully refreshed after clicking on Refreshing data button
 */
class AssertBestsellersOnDashboard extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that ordered products in bestsellers on Dashboard successfully refreshed
     *
     * @param OrderInjectable $order
     * @param Dashboard $dashboard
     * @return void
     */
    public function processAssert(OrderInjectable $order, Dashboard $dashboard)
    {
        $dashboard->open();
        $dashboard->getStoreStatsBlock()->refreshData();
        /** @var \Magento\Backend\Test\Block\Dashboard\Tab\Products\Ordered $bestsellersGrid */
        $bestsellersGrid = $dashboard->getStoreStatsBlock()->getTabElement('bestsellers')->getBestsellersGrid();
        $products = $order->getEntityId()['products'];
        foreach($products as $product) {
            $filter = [
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'qty' => $product->getCheckoutData()['qty'],
            ];
            \PHPUnit_Framework_Assert::assertTrue(
                $bestsellersGrid->isProductVisible($filter),
                'Bestseller does not present in report grid after refresh data.'
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Bestsellers successfully updated after Refreshing data.';
    }
}
