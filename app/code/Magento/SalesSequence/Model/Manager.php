<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Model;

use Magento\Framework\DB\Sequence\SequenceInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\SalesSequence\Model\ResourceModel\Meta as ResourceSequenceMeta;

/**
 * Class Manager
 * @api
 * @since 100.0.2
 */
class Manager
{
    /**
     * @param ResourceSequenceMeta $resourceSequenceMeta
     * @param SequenceFactory $sequenceFactory
     */
    public function __construct(
        protected readonly ResourceSequenceMeta $resourceSequenceMeta,
        protected readonly SequenceFactory $sequenceFactory
    ) {
    }

    /**
     * Returns sequence for given entityType and store
     *
     * @param string $entityType
     * @param int $storeId
     *
     * @return SequenceInterface
     * @throws LocalizedException
     */
    public function getSequence($entityType, $storeId)
    {
        return $this->sequenceFactory->create(
            [
                'meta' => $this->resourceSequenceMeta->loadByEntityTypeAndStore(
                    $entityType,
                    $storeId
                )
            ]
        );
    }
}
