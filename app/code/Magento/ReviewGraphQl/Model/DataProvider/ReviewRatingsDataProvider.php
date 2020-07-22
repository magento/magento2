<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReviewGraphQl\Model\DataProvider;

use Magento\Review\Model\ResourceModel\Rating\Option\Vote\Collection as VoteCollection;
use Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory as VoteCollectionFactory;

/**
 * Provides rating votes
 */
class ReviewRatingsDataProvider
{
    /**
     * @var VoteCollectionFactory
     */
    private $voteCollectionFactory;

    /**
     * @param VoteCollectionFactory $voteCollectionFactory
     */
    public function __construct(VoteCollectionFactory $voteCollectionFactory)
    {
        $this->voteCollectionFactory = $voteCollectionFactory;
    }

    /**
     * Providing rating votes
     *
     * @param int $reviewId
     *
     * @return array
     */
    public function getData(int $reviewId): array
    {
        /** @var VoteCollection $ratingVotes */
        $ratingVotes = $this->voteCollectionFactory->create();
        $ratingVotes->setReviewFilter($reviewId);
        $ratingVotes->addRatingInfo();

        $data = [];

        foreach ($ratingVotes->getItems() as $ratingVote) {
            $data[] = [
                'name' => $ratingVote->getData('rating_code'),
                'value' => $ratingVote->getData('value')
            ];
        }

        return $data;
    }
}
