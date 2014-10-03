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

use Magento\Reports\Test\Page\Adminhtml\DownloadsReport;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertDownloadsReportResult
 * Assert downloads product info in report grid
 */
class AssertDownloadsReportResult extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert product info in report: product name, link title, sku, downloads number (Reports > Products > Downloads)
     *
     * @param OrderInjectable $order
     * @param DownloadsReport $downloadsReport
     * @param int $downloads
     * @return void
     */
    public function processAssert(OrderInjectable $order, DownloadsReport $downloadsReport, $downloads)
    {
        $downloadsReport->open();
        foreach ($order->getEntityId()['products'] as $product) {
            foreach ($product->getDownloadableLinks()['downloadable']['link'] as $link) {
                $filter = [
                    'name' => $product->getName(),
                    'link_title' => $link['title'],
                    'sku' => $product->getSku(),
                ];
                $downloadsReport->getGridBlock()->search($filter);
                $filter[] = $downloads;
                \PHPUnit_Framework_Assert::assertTrue(
                    $downloadsReport->getGridBlock()->isRowVisible($filter, false),
                    "Downloads report link {$link['title']} is not present in reports grid."
                );
            }
        }
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Downloads report is present in reports grid.';
    }
}
