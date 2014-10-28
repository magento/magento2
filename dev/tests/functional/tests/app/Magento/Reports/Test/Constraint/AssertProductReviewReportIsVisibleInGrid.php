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
use Magento\Reports\Test\Page\Adminhtml\ProductReportReview;

/**
 * Class AssertProductReviewReportIsVisibleInGrid
 * Assert that Product Review Report is visible in reports grid
 */
class AssertProductReviewReportIsVisibleInGrid extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that Product Review Report is visible in reports grid
     *
     * @param ProductReportReview $productReportReview
     * @param ReviewInjectable $review
     * @return void
     */
    public function processAssert(ProductReportReview $productReportReview, ReviewInjectable $review)
    {
        $productReportReview->open();
        $name = $review->getDataFieldConfig('entity_id')['source']->getEntity()->getName();
        \PHPUnit_Framework_Assert::assertTrue(
            $productReportReview->getGridBlock()->isRowVisible(['title' => $name], false),
            'Review for ' . $name . ' product is not visible in reports grid.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Product Review Report is visible in reports grid.';
    }
}
