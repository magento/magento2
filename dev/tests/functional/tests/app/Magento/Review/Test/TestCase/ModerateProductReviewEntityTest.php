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

namespace Magento\Review\Test\TestCase;

use Mtf\TestCase\Injectable;
use Magento\Review\Test\Fixture\ReviewInjectable;
use Magento\Review\Test\Page\Adminhtml\ReviewEdit;
use Magento\Review\Test\Page\Adminhtml\ReviewIndex;

/**
 * Test Creation for Moderate ProductReview Entity
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create product
 * 2. Create product review
 *
 * Steps:
 * 1. Login to backend
 * 2. Open Marketing -> Reviews
 * 3. Search and open review created in precondition
 * 4. Fill data according to dataset
 * 5. Save
 * 6. Perform all assertions
 *
 * @group Reviews_and_Ratings_(MX)
 * @ZephyrId MAGETWO-26768
 */
class ModerateProductReviewEntityTest extends Injectable
{
    /**
     * Backend review grid page
     *
     * @var ReviewIndex
     */
    protected $reviewIndex;

    /**
     * Backend review edit page
     *
     * @var ReviewEdit
     */
    protected $reviewEdit;

    /**
     * Injection pages
     *
     * @param ReviewIndex $reviewIndex
     * @param ReviewEdit $reviewEdit
     * @return void
     */
    public function __inject(ReviewIndex $reviewIndex, ReviewEdit $reviewEdit)
    {
        $this->reviewIndex = $reviewIndex;
        $this->reviewEdit = $reviewEdit;
    }

    /**
     * Run moderate product review test
     *
     * @param ReviewInjectable $reviewInitial
     * @param ReviewInjectable $review
     * @return array
     */
    public function test(ReviewInjectable $reviewInitial, ReviewInjectable $review)
    {
        // Precondition
        $reviewInitial->persist();

        // Steps
        $this->reviewIndex->open();
        $this->reviewIndex->getReviewGrid()->searchAndOpen(['review_id' => $reviewInitial->getReviewId()]);
        $this->reviewEdit->getReviewForm()->fill($review);
        $this->reviewEdit->getPageActions()->save();

        // Prepare data for asserts
        $product = $reviewInitial->getDataFieldConfig('entity_id')['source']->getEntity();

        return ['product' => $product];
    }
}
