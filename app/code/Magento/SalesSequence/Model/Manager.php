<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Model;

use Magento\SalesSequence\Model\ResourceModel\Meta as ResourceSequenceMeta;

/**
 * Class Manager
 * @api
 * @since 100.0.2
 */
class Manager
{
    /**
     * @var ResourceSequenceMeta
     */
    protected $resourceSequenceMeta;

    /**
     * @var SequenceFactory
     */
    protected $sequenceFactory;

    /**
     * @param ResourceSequenceMeta $resourceSequenceMeta
     * @param SequenceFactory $sequenceFactory
     */
    public function __construct(
        ResourceSequenceMeta $resourceSequenceMeta,
        SequenceFactory $sequenceFactory
    ) {
        $this->resourceSequenceMeta = $resourceSequenceMeta;
        $this->sequenceFactory = $sequenceFactory;
    }

    /**
     * Returns sequence for given entityType and store
     *
     * @param string $entityType
     * @param int $storeId
     *
     * @return \Magento\Framework\DB\Sequence\SequenceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
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
