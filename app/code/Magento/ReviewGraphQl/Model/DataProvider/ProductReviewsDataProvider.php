<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReviewGraphQl\Model\DataProvider;

use Magento\Review\Model\ResourceModel\Review\Collection;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;
use Magento\Review\Model\Review;

/**
 * Provides product reviews
 */
class ProductReviewsDataProvider
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get product reviews
     *
     * @param int $productId
     * @param int $currentPage
     * @param int $pageSize
     *
     * @return Collection
     */
    public function getData(int $productId, int $currentPage, int $pageSize): Collection
    {
        /** @var Collection $reviewsCollection */
        $reviewsCollection = $this->collectionFactory->create()
            ->addStatusFilter(Review::STATUS_APPROVED)
            ->addEntityFilter(Review::ENTITY_PRODUCT_CODE, $productId)
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
