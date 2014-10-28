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

namespace Magento\Reports\Test\Constraint;

use Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Reports\Test\Page\Adminhtml\Bestsellers;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;

/**
 * Class AssertBestsellerReportResult
 * Assert bestseller info in report: date, product name and qty
 */
class AssertBestsellerReportResult extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert bestseller info in report: date, product name and qty
     *
     * @param Bestsellers $bestsellers
     * @param OrderInjectable $order
     * @param string $date
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function processAssert(Bestsellers $bestsellers, OrderInjectable $order, $date)
    {
        $products = $order->getEntityId()['products'];
        $totalQuantity = $bestsellers->getGridBlock()->getViewsResults($products, $date);
        $productQty = [];
        foreach ($products as $key => $product) {
            /** @var CatalogProductSimple $product*/
            $productQty[$key] = $product->getCheckoutData()['qty'];
        }
        \PHPUnit_Framework_Assert::assertEquals($productQty, $totalQuantity);
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Bestseller total result is equals to data from dataSet.';
    }
}
