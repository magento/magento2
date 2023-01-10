<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReviewGraphQl\Model\DataProvider;

use Magento\Review\Model\ResourceModel\Review\Collection as ReviewsCollection;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewsCollectionFactory;
use Magento\Review\Model\Review;

/**
 * Provides customer reviews
 */
class CustomerReviewsDataProvider
{
    /**
     * @var ReviewsCollectionFactory
     */
    private $collectionFactory;

    /**
     * @param ReviewsCollectionFactory $collectionFactory
     */
    public function __construct(
        ReviewsCollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get customer reviews
     *
     * @param int $customerId
     * @param int $currentPage
     * @param int $pageSize
     *
     * @return ReviewsCollection
     */
    public function getData(int $customerId, int $currentPage, int $pageSize): ReviewsCollection
    {
        /** @var ReviewsCollection $reviewsCollection */
        $reviewsCollection = $this->collectionFactory->create();
        $reviewsCollection
            ->addCustomerFilter($customerId)
            ->setPageSize($pageSize)
            ->setCurPage($currentPage)
            ->setDateOrder();
        $reviewsCollection->getSelect()->join(
            ['cpe' => $reviewsCollection->getTable('catalog_product_entity')],
            'cpe.entity_id = main_table.entity_pk_value',
            ['sku']
        );
        $reviewsCollection->addRateVotes();

        return $reviewsCollection;
    }
}
