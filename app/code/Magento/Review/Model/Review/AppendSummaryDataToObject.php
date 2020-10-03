<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Model\Review;

use Magento\Framework\Model\AbstractModel;
use Magento\Review\Model\ResourceModel\Review as ReviewResource;
use Magento\Review\Model\ResourceModel\Review\Summary\CollectionFactory as SummaryCollectionFactory;
use Magento\Review\Model\Review;

class AppendSummaryDataToObject
{
    /**
     * @var SummaryCollectionFactory
     */
    private $summaryCollectionFactory;

    /**
     * @var ReviewResource
     */
    private $reviewResource;

    /**
     * @param SummaryCollectionFactory $sumColFactory
     * @param ReviewResource $reviewResource
     */
    public function __construct(
        SummaryCollectionFactory $sumColFactory,
        ReviewResource $reviewResource
    ) {
        $this->summaryCollectionFactory = $sumColFactory;
        $this->reviewResource = $reviewResource;
    }

    /**
     * Append review summary data to product
     *
     * @param AbstractModel $object
     * @param int $storeId
     * @param string $entityCode
     */
    public function execute(
        AbstractModel $object,
        int $storeId,
        string $entityCode = Review::ENTITY_PRODUCT_CODE
    ): void {
        $summary = $this->summaryCollectionFactory->create()
            ->addEntityFilter($object->getId(), $this->reviewResource->getEntityIdByCode($entityCode))
            ->addStoreFilter($storeId)
            ->getFirstItem();
        $object->addData(
            [
                'reviews_count' => $summary->getData('reviews_count'),
                'rating_summary' => $summary->getData('rating_summary')
            ]
        );
    }
}
