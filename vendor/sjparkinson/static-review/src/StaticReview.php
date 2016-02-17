<?php

/*
 * This file is part of StaticReview
 *
 * Copyright (c) 2014 Samuel Parkinson <@samparkinson_>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see http://github.com/sjparkinson/static-review/blob/master/LICENSE.md
 */

namespace StaticReview;

use StaticReview\Collection\FileCollection;
use StaticReview\Collection\ReviewCollection;
use StaticReview\Reporter\ReporterInterface;
use StaticReview\Review\ReviewInterface;

class StaticReview
{
    /**
     * A ReviewCollection.
     */
    protected $reviews;

    /**
     * A Reporter.
     */
    protected $reporter;

    /**
     * Initializes a new instance of the StaticReview class.
     *
     * @param ReporterInterface $reporter
     */
    public function __construct(ReporterInterface $reporter)
    {
        $this->reviews = new ReviewCollection();

        $this->setReporter($reporter);
    }

    /**
     * Gets the ReporterInterface instance.
     *
     * @return ReporterInterface
     */
    public function getReporter()
    {
        return $this->reporter;
    }

    /**
     * Sets the ReporterInterface instance.
     *
     * @param  ReporterInterface $reporter
     * @return StaticReview
     */
    public function setReporter(ReporterInterface $reporter)
    {
        $this->reporter = $reporter;

        return $this;
    }

    /**
     * Returns the list of reviews.
     *
     * @return ReviewCollection
     */
    public function getReviews()
    {
        return $this->reviews;
    }

    /**
     * Adds a Review to be run.
     *
     * @param  ReviewInterface $check
     * @return StaticReview
     */
    public function addReview(ReviewInterface $review)
    {
        $this->reviews->append($review);

        return $this;
    }

    /**
     * Appends a ReviewCollection to the current list of reviews.
     *
     * @param  ReviewCollection $checks
     * @return StaticReview
     */
    public function addReviews(ReviewCollection $reviews)
    {
        foreach ($reviews as $review) {
            $this->reviews->append($review);
        }

        return $this;
    }

    /**
     * Runs through each review on each file, collecting any errors.
     *
     * @return StaticReview
     */
    public function review(FileCollection $files)
    {
        foreach ($files as $key => $file) {

            $this->getReporter()->progress($key + 1, count($files));

            foreach ($this->getReviews()->forFile($file) as $review) {
                $review->review($this->getReporter(), $file);
            }

        }

        return $this;
    }
}
