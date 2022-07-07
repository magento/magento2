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
 * Add review summary data to object by its entity code
 */
class AppendSummaryData
{
    /**
     * @var SummaryCollectionFactory
     */
    private $summaryCollectionFactory;

    /**
     * @param SummaryCollectionFactory $summaryCollectionFactory
     */
    public function __construct(
        SummaryCollectionFactory $summaryCollectionFactory
    ) {
        $this->summaryCollectionFactory = $summaryCollectionFactory;
    }

    /**
     * Append summary data to object filtered by its entity code
     *
     * @param AbstractModel $object
     * @param int $storeId
     * @param string $entityCode
     * @retrun void
     */
    public function execute(AbstractModel $object, int $storeId, string $entityCode): void
    {
        $summaryCollection = $this->summaryCollectionFactory->create();
        $summaryCollection->addStoreFilter($storeId);
        $summaryCollection->getSelect()
            ->joinLeft(
                ['review_entity' => $summaryCollection->getResource()->getTable('review_entity')],
                'main_table.entity_type = review_entity.entity_id',
                'entity_code'
            )
            ->where('entity_pk_value = ?', $object->getId())
            ->where('entity_code = ?', $entityCode);
        $summaryItem = $summaryCollection->getFirstItem();

        $object->addData(
            [
                'reviews_count' => $summaryItem->getData('reviews_count'),
                'rating_summary' => $summaryItem->getData('rating_summary'),
            ]
        );
    }
}
