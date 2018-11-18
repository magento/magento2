<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Model\Review;

use Magento\Review\Model\ResourceModel\Rating\CollectionFactory as RatingCollectionFactory;
use Magento\Review\Model\ResourceModel\Rating\Option\CollectionFactory as RatingOptionCollectionFactory;

/**
 * Class RatingsProcessor
 *
 * @package Magento_Review
 */
class RatingsProcessor implements RatingsProcessorInterface
{
    /**
     * @var array
     */
    private $ratingsByStore;

    /**
     * @var array
     */
    private $ratingOptionsByRating;

    /**
     * @var RatingCollectionFactory
     */
    private $ratingCollectionFactory;

    /**
     * @var RatingOptionCollectionFactory
     */
    private $ratingOptionCollectionFactory;

    /**
     * RatingsProcessor constructor
     *
     * @param RatingCollectionFactory $ratingCollectionFactory
     * @param RatingOptionCollectionFactory $ratingOptionCollectionFactory
     */
    public function __construct(
        RatingCollectionFactory $ratingCollectionFactory,
        RatingOptionCollectionFactory $ratingOptionCollectionFactory
    ) {
        $this->ratingCollectionFactory = $ratingCollectionFactory;
        $this->ratingOptionCollectionFactory = $ratingOptionCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function getRatingIdByName(string $ratingName, int $storeId): ?int
    {
        if (!isset($this->ratingsByStore[$storeId])) {
            $collection = $this->ratingCollectionFactory->create();
            $collection->setStoreFilter($storeId);

            $this->ratingsByStore[$storeId] = $collection->toOptionHash();
        }

        $ratingId = array_search($ratingName, $this->ratingsByStore[$storeId]);

        return $ratingId !== false ? (int)$ratingId : null;
    }

    /**
     * @inheritdoc
     */
    public function getOptionIdByRatingIdAndValue(int $ratingId, int $value): ?int
    {
        if (!isset($this->ratingOptionsByRating[$ratingId])) {
            $collection = $this->ratingOptionCollectionFactory->create();
            $collection->addRatingFilter($ratingId);

            $this->ratingOptionsByRating[$ratingId] = $collection->toOptionHash();
        }

        $optionId = array_search($value, $this->ratingOptionsByRating[$ratingId]);

        return $optionId !== false ? (int)$optionId : null;
    }
}
