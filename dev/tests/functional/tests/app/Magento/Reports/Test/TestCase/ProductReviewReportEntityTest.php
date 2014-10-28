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
use Magento\Review\Test\Fixture\ReviewInjectable;

/**
 * Test Creation for ProductReviewReportEntity
 *
 * Preconditions:
 * 1. Create simple product
 * 2. Create review for this product
 *
 * Test Flow:
 * 1. Login as admin
 * 2. Navigate to the Reports>Reviews>By Products
 * 3. Perform appropriate assertions.
 *
 * @group Reports_(MX)
 * @ZephyrId MAGETWO-27223
 */
class ProductReviewReportEntityTest extends Injectable
{
    /**
     * Creation product review report entity
     *
     * @param ReviewInjectable $review
     * @return void
     */
    public function test(ReviewInjectable $review)
    {
        // Preconditions
        $review->persist();
    }
}
