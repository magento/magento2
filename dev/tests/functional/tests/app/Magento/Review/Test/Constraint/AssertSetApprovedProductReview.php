<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Review\Test\Fixture\Review;
use Magento\Review\Test\Page\Adminhtml\ReviewIndex;
use Magento\Backend\Test\Page\Adminhtml\AdminCache;
use Magento\Review\Test\Page\Adminhtml\ReviewEdit;

/**
 * Assert that product review can do approved.
 */
class AssertSetApprovedProductReview extends AbstractConstraint
{
    /**
     * Constraint severeness.
     *
     * @var string
     */
    protected $severeness = 'middle';

    /**
     * Admin cache page.
     *
     * @var AdminCache
     */
    protected $cachePage;

    /**
     * Assert that product review can do approved.
     *
     * @param ReviewIndex $reviewIndex
     * @param Review $review
     * @param ReviewEdit $reviewEdit
     * @param AssertReviewSuccessSaveMessage $assertReviewSuccessSaveMessage
     * @param AdminCache $cachePage
     * @return void
     */
    public function processAssert(
        ReviewIndex $reviewIndex,
        Review $review,
        ReviewEdit $reviewEdit,
        AssertReviewSuccessSaveMessage $assertReviewSuccessSaveMessage,
        AdminCache $cachePage
    ) {
        $this->cachePage = $cachePage;
        $reviewIndex->open();
        $reviewGrid = $reviewIndex->getReviewGrid();
        $reviewGrid->searchAndOpen(['title' => $review->getTitle()]);

        $reviewEdit->getReviewForm()->setApproveReview();
        $reviewEdit->getPageActions()->save();

        $assertReviewSuccessSaveMessage->processAssert($reviewIndex);
        $this->flushCacheStorageWithAssert();
    }

    /**
     * Flush cache storage and assert success message.
     *
     * @return void
     */
    protected function flushCacheStorageWithAssert()
    {
        $this->cachePage->open();
        $this->cachePage->getActionsBlock()->flushCacheStorage();
        $this->cachePage->getModalBlock()->acceptAlert();
        \PHPUnit_Framework_Assert::assertTrue(
            $this->cachePage->getActionsBlock()->isStorageCacheFlushed(),
            'Cache is not flushed.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Review status is change to approve.';
    }
}
