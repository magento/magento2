<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Constraint;

use Magento\Review\Test\Fixture\Review;
use Magento\Review\Test\Page\Adminhtml\ReviewEdit;
use Magento\Review\Test\Page\Adminhtml\ReviewIndex;
use Magento\Mtf\Constraint\AbstractAssertForm;

/**
 * Class AssertProductRatingOnReviewPage
 */
class AssertProductRatingOnReviewPage extends AbstractAssertForm
{
    /**
     * Assert that product rating is displayed on product review(backend)
     *
     * @param ReviewIndex $reviewIndex
     * @param ReviewEdit $reviewEdit
     * @param Review $review
     * @param Review|null $reviewInitial [optional]
     * @return void
     */
    public function processAssert(
        ReviewIndex $reviewIndex,
        ReviewEdit $reviewEdit,
        Review $review,
        Review $reviewInitial = null
    ) {
        $filter = ['title' => $review->getTitle()];

        $reviewIndex->open();
        $reviewIndex->getReviewGrid()->searchAndOpen($filter);

        $ratingReview = array_replace(
            ($reviewInitial && $reviewInitial->hasData('ratings')) ? $reviewInitial->getRatings() : [],
            $review->hasData('ratings') ? $review->getRatings() : []
        );
        $ratingReview = $this->sortDataByPath($ratingReview, '::title');
        $ratingForm = $reviewEdit->getReviewForm()->getData();
        $ratingForm = $this->sortDataByPath($ratingForm['ratings'], '::title');
        $error = $this->verifyData($ratingReview, $ratingForm);
        \PHPUnit\Framework\Assert::assertTrue(empty($error), $error);
    }

    /**
     * Text success product rating is displayed on edit review page(backend)
     *
     * @return string
     */
    public function toString()
    {
        return 'Product rating is displayed on edit review page(backend).';
    }
}
