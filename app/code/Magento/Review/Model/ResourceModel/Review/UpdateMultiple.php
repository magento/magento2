<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Model\ResourceModel\Review;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory as VoteCollectionFactory;
use Magento\Review\Model\ResourceModel\Review as ReviewResource;
use Magento\Review\Model\Review;
use Magento\Review\Model\Review\RatingsProcessorInterface;
use Magento\ReviewApi\Api\Data\ReviewInterface;
use Magento\Store\Model\Store;

/**
 * Class UpdateMultiple
 */
class UpdateMultiple
{
    /**
     * @var string
     */
    private $reviewTableName;

    /**
     * @var string
     */
    private $reviewDetailTableName;

    /**
     * @var string
     */
    private $reviewStoreTable;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var RatingsProcessorInterface
     */
    private $ratingsProcessor;

    /**
     * Rating model factory
     *
     * @var RatingFactory
     */
    private $ratingFactory;

    /**
     * @var VoteCollectionFactory
     */
    private $voteCollectionFactory;

    /**
     * CreateMultiple constructor
     *
     * @param ResourceConnection $resourceConnection
     * @param RatingsProcessorInterface $ratingsProcessor
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        RatingsProcessorInterface $ratingsProcessor
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->ratingsProcessor = $ratingsProcessor;

        $this->reviewTableName = $resourceConnection->getConnection()->getTableName(
            ReviewResource::TABLE_NAME_REVIEW
        );
        $this->reviewDetailTableName = $resourceConnection->getConnection()->getTableName(
            ReviewResource::TABLE_NAME_REVIEW_DETAIL
        );
        $this->reviewStoreTable = $resourceConnection->getConnection()->getTableName(
            ReviewResource::TABLE_NAME_REVIEW_STORE
        );

        // TODO: remove when implementing review rating save using SQL QUERIES
        $this->ratingFactory = ObjectManager::getInstance()->get(RatingFactory::class);
        $this->voteCollectionFactory = ObjectManager::getInstance()->get(VoteCollectionFactory::class);
    }

    /**
     * Create reviews
     *
     * @param ReviewInterface[] $reviews
     * @throws \Exception
     */
    public function execute(array $reviews)
    {
        if (!count($reviews)) {
            return;
        }

        foreach ($reviews as $review) {
            $this->updateReview($review);

            $stores = array_unique(array_merge($review->getStores(), [$review->getStoreId(), Store::DEFAULT_STORE_ID]));
            $review->setStores($stores);

            $this->updateReviewDetail($review);
            $this->updateReviewStores($review);

            if (!empty($review->getRatings())) {
                $this->updateRatings($review);
            }
        }
    }

    /**
     * Update review
     *
     * @param ReviewInterface $review
     */
    private function updateReview(ReviewInterface $review)
    {
        $connection = $this->resourceConnection->getConnection();

        $reviewBindData = [
            ReviewInterFace::STATUS => $review->getStatus() ?: Review::STATUS_PENDING,
        ];
        $where = sprintf(
            '%s=%d',
            $connection->quoteIdentifier(ReviewInterFace::REVIEW_ID),
            $review->getReviewId()
        );

        $connection->update($this->reviewTableName, $reviewBindData, $where);
    }

    /**
     * Update review detail
     *
     * @param ReviewInterface $review
     */
    private function updateReviewDetail(ReviewInterface $review)
    {
        $connection = $this->resourceConnection->getConnection();

        $reviewDetailBindData = [
            ReviewInterFace::TITLE => $review->getTitle(),
            ReviewInterFace::REVIEW_TEXT => $review->getReviewText(),
            ReviewInterFace::CUSTOMER_NICKNAME => $review->getCustomerNickname(),
        ];
        $where = sprintf(
            '%s=%d',
            $connection->quoteIdentifier(ReviewInterFace::REVIEW_ID),
            $review->getReviewId()
        );

        $connection->update($this->reviewDetailTableName, $reviewDetailBindData, $where);
    }

    /**
     * Update review stores
     *
     * @param ReviewInterface $review
     */
    private function updateReviewStores(ReviewInterface $review)
    {
        $this->deleteReviewStores($review);
        $this->insertReviewStores($review);
    }

    /**
     * Delete review store data
     *
     * @param ReviewInterface $review
     */
    private function deleteReviewStores(ReviewInterface $review)
    {
        $connection = $this->resourceConnection->getConnection();

        $where = sprintf('%s=%d', ReviewInterface::REVIEW_ID, $review->getReviewId());
        $connection->delete($this->reviewStoreTable, $where);
    }

    /**
     * Insert review stores
     *
     * @param ReviewInterface $review
     */
    private function insertReviewStores(ReviewInterface $review)
    {
        $connection = $this->resourceConnection->getConnection();

        $reviewStoreBindData = [];
        foreach ($review->getStores() as $storeId) {
            $reviewStoreBindData[] = [
                ReviewInterFace::REVIEW_ID => $review->getReviewId(),
                ReviewInterFace::STORE_ID => $storeId,
            ];
        }

        $connection->insertMultiple($this->reviewStoreTable, $reviewStoreBindData);
    }

    /**
     * Update ratings
     *
     * @param ReviewInterface $review
     */
    private function updateRatings(ReviewInterface $review)
    {
        $votes = $this->voteCollectionFactory->create()
            ->setReviewFilter($review->getReviewId())
            ->addOptionInfo()
            ->load()
            ->addRatingOptions();

        foreach ($review->getRatings() as $rating) {
            $ratingId = $this->ratingsProcessor->getRatingIdByName($rating->getRatingName(), $review->getStoreId());
            $optionId = $this->ratingsProcessor->getOptionIdByRatingIdAndValue($ratingId, $rating->getRatingValue());

            $rating->setRatingId($ratingId);
            $rating->setRatingOptionId($optionId);

            if ($vote = $votes->getItemByColumnValue('rating_id', $ratingId)) {
                $this->ratingFactory->create()
                    ->setVoteId($vote->getId())
                    ->setReviewId($review->getReviewId())
                    ->updateOptionVote($optionId);
            } else {
                $this->ratingFactory->create()
                    ->setRatingId($ratingId)
                    ->setReviewId($review->getReviewId())
                    ->addOptionVote($optionId, $review->getRelatedEntityId());
            }
        }
    }
}
