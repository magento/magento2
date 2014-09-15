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

use Magento\Reports\Test\Page\Adminhtml\ProductReportView;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductViewsReportTotalResult
 * Assert product info in report: product name, price and views
 */
class AssertProductViewsReportTotalResult extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert product info in report: product name, price and views
     *
     * @param ProductReportView $productReportView
     * @param string $total
     * @param array $productsList
     * @return void
     */
    public function processAssert(ProductReportView $productReportView, $total, array $productsList)
    {
        $total = explode(', ', $total);
        $totalForm = $productReportView->getGridBlock()->getViewsResults($productsList);
        \PHPUnit_Framework_Assert::assertEquals($totalForm, $total);
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Products view total result is equals to data from dataSet.';
    }
}
