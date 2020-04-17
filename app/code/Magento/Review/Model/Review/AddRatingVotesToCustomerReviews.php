<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Model\Review;

use Magento\Review\Model\ResourceModel\Rating\Option\Vote\Collection as OptionVoteCollection;
use Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory as OptionVoteCollectionFactory;
use Magento\Review\Model\ResourceModel\Review\Product\Collection;

/**
 * The model that adds the rating votes to reviews
 */
class AddRatingVotesToCustomerReviews
{
    /**
     * @var RatingOptionCollectionFactory
     */
    private $ratingOptionCollectionFactory;

    /**
     * @param OptionVoteCollectionFactory $ratingOptionCollectionFactory
     */
    public function __construct(OptionVoteCollectionFactory $ratingOptionCollectionFactory)
    {
        $this->ratingOptionCollectionFactory = $ratingOptionCollectionFactory;
    }

    /**
     * Add rating votes to customer reviews
     *
     * @param Collection $collection
     */
    public function execute(Collection $collection): void
    {
        $connection = $collection->getConnection();

        foreach ($collection->getItems() as &$item) {
            /** @var OptionVoteCollection $votesCollection */
            $votesCollection = $this->ratingOptionCollectionFactory->create();

            $votesCollection->addFieldToFilter('main_table.review_id', $item->getData('review_id'));
            $votesCollection->getSelect()
                ->join(
                    ['rating' => $connection->getTableName('rating')],
                    'rating.rating_id = main_table.rating_id',
                    ['rating_code']
                );
            $item->setRatingVotes($votesCollection);
        }
    }
}
