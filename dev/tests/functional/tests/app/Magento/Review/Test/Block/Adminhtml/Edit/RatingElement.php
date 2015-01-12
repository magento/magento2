<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Block\Adminhtml\Edit;

use Mtf\Client\Driver\Selenium\Element;
use Mtf\Client\Element\Locator;

/**
 * Class RatingElement
 * Rating typified element
 */
class RatingElement extends Element
{
    /**
     * Rating selector
     *
     * @var string
     */
    protected $rating = './/*[@data-widget="ratingControl"]//label[contains(@for, "%s_%s")]';

    /**
     * Selector for label of checked rating
     *
     * @var string
     */
    protected $checkedRating = 'input[id$="_%d"]:checked + label';

    /**
     * Selector for single rating
     *
     * @var string
     */
    protected $ratingByNumber = './/*[@id="rating_detail"]//*[contains(@class,"field-rating")][%d]';

    /**
     * Set rating value
     *
     * @param array $value
     * @return void
     */
    public function setValue($value)
    {
        foreach ($value as $rating) {
            $ratingSelector = sprintf($this->rating, $rating['title'], $rating['rating']);
            $this->find($ratingSelector, Locator::SELECTOR_XPATH)->click();
        }
    }

    /**
     * Get rating vote
     *
     * @param Element $rating
     * @return int
     */
    protected function getRatingVote(Element $rating)
    {
        $ratingVote = 5;
        $ratingVoteElement = $rating->find(sprintf($this->checkedRating, $ratingVote));
        while (!$ratingVoteElement->isVisible() && $ratingVote) {
            --$ratingVote;
            $ratingVoteElement = $rating->find(sprintf($this->checkedRating, $ratingVote));
        }

        return $ratingVote;
    }

    /**
     * Get list ratings
     *
     * @return array
     */
    public function getValue()
    {
        $ratings = [];

        $count = 1;
        $rating = $this->find(sprintf($this->ratingByNumber, $count), Locator::SELECTOR_XPATH);
        while ($rating->isVisible()) {
            $ratings[$count] = [
                'title' => $rating->find('./label/span', Locator::SELECTOR_XPATH)->getText(),
                'rating' => $this->getRatingVote($rating),
            ];

            ++$count;
            $rating = $this->find(sprintf($this->ratingByNumber, $count), Locator::SELECTOR_XPATH);
        }
        return $ratings;
    }
}
