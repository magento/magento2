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
use Magento\Review\Model\ResourceModel\Review as ReviewResource;
use Magento\Review\Model\Review;
use Magento\ReviewApi\Api\Data\ReviewInterface;
use Magento\Store\Model\Store;

/**
 * Class CreateMultiple
 */
class CreateMultiple
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
     * @var Review\RatingsProcessorInterface
     */
    private $ratingsProcessor;

    /**
     * Rating model factory
     *
     * @var RatingFactory
     */
    private $ratingFactory;

    /**
     * CreateMultiple constructor
     *
     * @param ResourceConnection $resourceConnection
     * @param Review\RatingsProcessorInterface $ratingsProcessor
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        Review\RatingsProcessorInterface $ratingsProcessor
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
            $this->insertReview($review);

            $stores = array_unique(array_merge($review->getStores(), [$review->getStoreId(), Store::DEFAULT_STORE_ID]));
            $review->setStores($stores);

            $this->insertReviewDetail($review);
            $this->insertReviewStores($review);

            if (!empty($review->getRatings())) {
                $this->insertRatings($review);
            }
        }
    }

    /**
     * Insert review
     *
     * @param ReviewInterface $review
     */
    private function insertReview(ReviewInterface $review)
    {
        $connection = $this->resourceConnection->getConnection();

        $reviewBindData = [
            ReviewInterFace::REVIEW_ENTITY_ID => $review->getReviewEntityId(),
            ReviewInterFace::RELATED_ENTITY_ID => $review->getRelatedEntityId(),
            ReviewInterFace::STATUS => $review->getStatus() ?: Review::STATUS_PENDING,
        ];

        $connection->insert($this->reviewTableName, $reviewBindData);

        $reviewId = $connection->lastInsertId($this->reviewTableName);
        $review->setReviewId($reviewId);
    }

    /**
     * Insert review detail
     *
     * @param ReviewInterface $review
     */
    private function insertReviewDetail(ReviewInterface $review)
    {
        $connection = $this->resourceConnection->getConnection();

        $reviewDetailBindData = [
            ReviewInterFace::REVIEW_ID => $review->getReviewId(),
            ReviewInterFace::STORE_ID => $review->getStoreId(),
            ReviewInterFace::TITLE => $review->getTitle(),
            ReviewInterFace::REVIEW_TEXT => $review->getReviewText(),
            ReviewInterFace::CUSTOMER_NICKNAME => $review->getCustomerNickname(),
            ReviewInterFace::CUSTOMER_ID => $review->getCustomerId(),
        ];

        $connection->insert($this->reviewDetailTableName, $reviewDetailBindData);
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
     * Insert ratings
     *
     * @param ReviewInterface $review
     */
    private function insertRatings(ReviewInterface $review)
    {
        foreach ($review->getRatings() as $rating) {
            $ratingId = $this->ratingsProcessor->getRatingIdByName($rating->getRatingName(), $review->getStoreId());
            $optionId = $this->ratingsProcessor->getOptionIdByRatingIdAndValue($ratingId, $rating->getRatingValue());

            $rating->setRatingId($ratingId);
            $rating->setRatingOptionId($optionId);

            $this->ratingFactory->create()
                ->setRatingId($ratingId)
                ->setReviewId($review->getReviewId())
                ->addOptionVote($optionId, $review->getRelatedEntityId());
        }
    }
}
