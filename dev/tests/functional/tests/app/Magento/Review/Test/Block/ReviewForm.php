<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Block;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Review\Test\Fixture\Rating;
use Magento\Review\Test\Fixture\Review;
use Magento\Mtf\Block\Form as AbstractForm;

/**
 * Review form on frontend.
 */
class ReviewForm extends AbstractForm
{
    /**
     * Legend selector.
     *
     * @var string
     */
    protected $legendSelector = 'legend';

    /**
     * 'Submit' review button selector.
     *
     * @var string
     */
    protected $submitButton = '.action.submit';

    /**
     * Single product rating selector.
     *
     * @var string
     */
    protected $rating = './/*[@id="%s_rating_label"]';

    /**
     * Selector for label of rating vote.
     *
     * @var string
     */
    protected $ratingVoteLabel = './following-sibling::div[contains(@class,"vote")]/label[contains(@id,"_%d_label")]';

    /**
     * Submit review form.
     *
     * @return void
     */
    public function submit()
    {
        $this->_rootElement->find($this->submitButton, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Get legend.
     *
     * @return SimpleElement
     */
    public function getLegend()
    {
        return $this->_rootElement->find($this->legendSelector);
    }

    /**
     * Check rating element is visible.
     *
     * @param Rating $rating
     * @return bool
     */
    public function isVisibleRating(Rating $rating)
    {
        return $this->getRating($rating)->isVisible();
    }

    /**
     * Get single product rating.
     *
     * @param Rating $rating
     * @return SimpleElement
     */
    protected function getRating(Rating $rating)
    {
        return $this->_rootElement->find(sprintf($this->rating, $rating->getRatingCode()), Locator::SELECTOR_XPATH);
    }

    /**
     * Fill the review form.
     *
     * @param FixtureInterface $review
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fill(FixtureInterface $review, SimpleElement $element = null)
    {
        if ($review->hasData('ratings')) {
            $this->fillRatings($review->getRatings());
        }
        parent::fill($review, $element);
    }

    /**
     * Fill ratings on the review form.
     *
     * @param Rating[] $ratings
     * @return void
     */
    protected function fillRatings(array $ratings)
    {
        foreach ($ratings as $rating) {
            $this->setRating($rating['title'], $rating['rating']);
        }
    }

    /**
     * Set rating vote by rating code.
     *
     * @param string $ratingCode
     * @param string $ratingVote
     * @return void
     */
    protected function setRating($ratingCode, $ratingVote)
    {
        $rating = $this->_rootElement->find(sprintf($this->rating, $ratingCode), Locator::SELECTOR_XPATH);
        $rating->find(sprintf($this->ratingVoteLabel, $ratingVote), Locator::SELECTOR_XPATH)->click();
    }
}
