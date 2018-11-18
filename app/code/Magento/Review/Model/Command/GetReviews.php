<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Model\Command;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\EntityManager\Operation\Read\ReadExtensions;
use Magento\Review\Model\ResourceModel\Review\Collection;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;
use Magento\ReviewApi\Api\Data\RatingOptionVoteInterface;
use Magento\ReviewApi\Api\Data\RatingOptionVoteInterfaceFactory;
use Magento\ReviewApi\Api\Data\ReviewInterface;
use Magento\ReviewApi\Api\Data\ReviewInterfaceFactory;
use Magento\ReviewApi\Api\Data\ReviewSearchResultsInterface;
use Magento\ReviewApi\Api\Data\ReviewSearchResultsInterfaceFactory;
use Magento\ReviewApi\Api\GetReviewsInterface;

/**
 * Class GetReviews
 */
class GetReviews implements GetReviewsInterface
{
    /**
     * Review collection factory
     *
     * @var ReviewCollectionFactory
     */
    private $reviewCollectionFactory;

    /**
     * Search result interface factory
     *
     * @var ReviewSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * Review factory
     *
     * @var ReviewInterfaceFactory
     */
    private $reviewFactory;

    /**
     * @var RatingOptionVoteInterfaceFactory
     */
    private $ratingOptionVoteInterfaceFactory;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * Collection processor
     *
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * Extension attributes reader
     *
     * @var ReadExtensions
     */
    private $readExtensions;

    /**
     * Data object helper
     *
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * GetReview constructor
     *
     * @param ReviewCollectionFactory $reviewCollectionFactory
     * @param ReviewSearchResultsInterfaceFactory $searchResultsFactory
     * @param ReviewInterfaceFactory $reviewFactory
     * @param RatingOptionVoteInterfaceFactory $ratingOptionVoteInterfaceFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     * @param ReadExtensions $readExtensions
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        ReviewCollectionFactory $reviewCollectionFactory,
        ReviewSearchResultsInterfaceFactory $searchResultsFactory,
        ReviewInterfaceFactory $reviewFactory,
        RatingOptionVoteInterfaceFactory $ratingOptionVoteInterfaceFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        CollectionProcessorInterface $collectionProcessor,
        ReadExtensions $readExtensions,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->reviewFactory = $reviewFactory;
        $this->ratingOptionVoteInterfaceFactory = $ratingOptionVoteInterfaceFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor;
        $this->readExtensions = $readExtensions;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @inheritdoc
     */
    public function execute(
        SearchCriteriaInterface $searchCriteria
    ): SearchResultsInterface {
        /** @var Collection $collection */
        $collection = $this->reviewCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process($collection);
        $this->collectionProcessor->process($searchCriteria, $collection);

        $collection->addStoreData();

        $this->addExtensionAttributes($collection);

        /** @var ReviewSearchResultsInterface $searchResult */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setTotalCount($collection->getSize());

        $items = [];
        /** @var \Magento\Review\Model\Review $review */
        foreach ($collection as $review) {
            $itemData = $this->reviewFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $itemData,
                $review->toArray(),
                ReviewInterface::class
            );

            $items[$review->getId()] = $itemData;
        }

        $collection->addRateVotes();

        foreach ($collection as $review) {
            $ratings = [];
            /** @var \Magento\Review\Model\Rating\Option\Vote $rating */
            foreach ($review->getRatings() as $rating) {
                $ratingData = $this->ratingOptionVoteInterfaceFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $ratingData,
                    $rating->toArray(),
                    RatingOptionVoteInterface::class
                );

                $ratings[$rating->getId()] = $ratingData;
            }
            $items[$review->getId()][ReviewInterface::RATINGS] = $ratings;
        }

        $searchResults->setItems($items);

        return $searchResults;
    }

    /**
     * Add extension attributes to loaded items.
     *
     * @param Collection $collection
     * @return Collection
     */
    private function addExtensionAttributes(Collection $collection): Collection
    {
        foreach ($collection->getItems() as $item) {
            $this->readExtensions->execute($item);
        }
        return $collection;
    }
}
