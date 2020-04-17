<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReviewGraphQl\Model\DataProvider;

use Magento\Review\Model\ResourceModel\Review\Product\Collection as ProductReviewsCollection;
use Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory;
use Magento\Review\Model\Review\AddRatingVotesToCustomerReviews;

/**
 * Provides customer reviews
 */
class CustomerReviewsDataProvider
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var AddRatingVotesToCustomerReviews
     */
    private $addRatingVotesToCustomerReviews;

    /**
     * @param CollectionFactory $collectionFactory
     * @param AddRatingVotesToCustomerReviews $addRatingVotesToCustomerReviews
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        AddRatingVotesToCustomerReviews $addRatingVotesToCustomerReviews
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->addRatingVotesToCustomerReviews = $addRatingVotesToCustomerReviews;
    }

    /**
     * Get customer reviews
     *
     * @param int $customerId
     * @param int $currentPage
     * @param int $pageSize
     *
     * @return ProductReviewsCollection
     */
    public function getData(int $customerId, int $currentPage, int $pageSize): ProductReviewsCollection
    {
        /** @var ProductReviewsCollection $reviewsCollection */
        $reviewsCollection = $this->collectionFactory->create();
        $reviewsCollection->addCustomerFilter($customerId)
            ->setPageSize($pageSize)
            ->setCurPage($currentPage)
            ->setDateOrder();
        $this->addRatingVotesToCustomerReviews->execute($reviewsCollection);

        return $reviewsCollection;
    }
}
