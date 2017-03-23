<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Constraint;

use Magento\Mtf\Constraint\AbstractAssertForm;
use Magento\Review\Test\Fixture\Review;
use Magento\Review\Test\Page\Adminhtml\ReviewIndex;
use Magento\Review\Test\Page\Adminhtml\ReviewEdit;

/**
 * Assert that review data on edit page equals passed from fixture.
 */
class AssertProductReviewForm extends AbstractAssertForm
{
    /**
     * Constraint severeness.
     *
     * @var string
     */
    protected $severeness = 'middle';

    /**
     * Skipped fields for verify data.
     *
     * @var array
     */
    protected $skippedFields = [
        'entity_id'
    ];

    /**
     * Assert that review data on edit page equals passed from fixture.
     *
     * @param ReviewIndex $reviewIndex
     * @param Review $review
     * @param ReviewEdit $reviewEdit
     * @return void
     */
    public function processAssert(ReviewIndex $reviewIndex, Review $review, ReviewEdit $reviewEdit)
    {
        $reviewIndex->open();
        $reviewGrid = $reviewIndex->getReviewGrid();
        $reviewGrid->searchAndOpen(['title' => $review->getTitle()]);

        $fixtureData = $review->getData();
        $formData = $reviewEdit->getReviewForm()->getData();
        if (isset($fixtureData['type'])) {
            $formData['type'] = $reviewEdit->getReviewForm()->getPostedBy();
        }

        $error = $this->verifyData($fixtureData, $formData);

        \PHPUnit_Framework_Assert::assertEmpty($error, $error);
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Review data on edit page equals passed from fixture.';
    }
}
