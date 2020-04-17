<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReviewGraphQl\Model\DataProvider;

/**
 * Provides rating votes
 */
class ReviewRatingsDataProvider
{
    /**
     * Providing rating votes
     *
     * @param array $ratingVotes
     *
     * @return array
     */
    public function getData(array $ratingVotes): array
    {
        $data = [];

        foreach ($ratingVotes as $ratingVote) {
            $data[] = [
                'name' => $ratingVote->getData('rating_code'),
                'value' => $ratingVote->getData('value')
            ];
        }

        return $data;
    }
}
