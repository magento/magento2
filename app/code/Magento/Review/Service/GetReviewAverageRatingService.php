<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Service;

/**
 * Get review average rating
 */
class GetReviewAverageRatingService
{
    /**
     * Get average rating per review
     *
     * @param array $ratingVotes
     *
     * @return float
     */
    public function execute(array $ratingVotes): float
    {
        $averageRating = 0;

        foreach ($ratingVotes as $ratingVote) {
            $averageRating += (int) $ratingVote->getData('value');
        }

        return $averageRating > 0 ? (float) number_format($averageRating / count($ratingVotes), 2) : 0;
    }
}
