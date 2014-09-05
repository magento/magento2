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
use Magento\Review\Test\Fixture\ReviewInjectable;
use Magento\Review\Test\Page\Adminhtml\ReviewIndex;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Reports\Test\Page\Adminhtml\CustomerReportReview;
use Magento\Review\Test\Constraint\AssertProductReviewInGrid;

/**
 * Class AssertProductReportByCustomerInGrid
 * Check that Customer review is displayed in grid
 */
class AssertProductReportByCustomerInGrid extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that Customer review is displayed in grid
     *
     * @param ReviewIndex $reviewIndex
     * @param ReviewInjectable $review
     * @param AssertProductReviewInGrid $assertProductReviewInGrid
     * @param CustomerReportReview $customerReportReview
     * @param CustomerInjectable $customer
     * @param CatalogProductSimple $product
     * @param string $gridStatus
     * @return void
     */
    public function processAssert(
        ReviewIndex $reviewIndex,
        ReviewInjectable $review,
        AssertProductReviewInGrid $assertProductReviewInGrid,
        CustomerReportReview $customerReportReview,
        CustomerInjectable $customer,
        CatalogProductSimple $product = null,
        $gridStatus = ''
    ) {
        $filter = $assertProductReviewInGrid->prepareFilter($product, $review, $gridStatus);

        $customerReportReview->open();
        $customerReportReview->getGridBlock()->openReview($customer);
        $reviewIndex->getReviewGrid()->search($filter);
        unset($filter['visible_in']);
        \PHPUnit_Framework_Assert::assertTrue(
            $reviewIndex->getReviewGrid()->isRowVisible($filter, false),
            'Customer review is absent in Review grid.'
        );
    }

    /**
     * Text success exist review in grid on product reviews tab
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer review is present in grid on product reviews tab.';
    }
}
