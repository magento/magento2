<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Review\Model\ResourceModel\Review\Summary\CollectionFactory as SummaryCollectionFactory;

/**
 * ReviewSummary model.
 */
class ReviewSummary
{
    /**
     * @var SummaryCollectionFactory
     */
    private $summaryCollectionFactory;

    /**
     * @param SummaryCollectionFactory $sumColFactory
     */
    public function __construct(
        SummaryCollectionFactory $sumColFactory
    ) {
        $this->summaryCollectionFactory = $sumColFactory;
    }

    /**
     * Append review summary data to product
     *
     * @param AbstractModel $object
     * @param int $storeId
     * @param int $entityType
     */
    public function appendSummaryDataToObject(AbstractModel $object, int $storeId, int $entityType = 1): void
    {
        $summary = $this->summaryCollectionFactory->create()
            ->addEntityFilter($object->getId(), $entityType)
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
