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
 * @since 2.0.0
 */
class Manager
{
    /**
     * @var ResourceSequenceMeta
     * @since 2.0.0
     */
    protected $resourceSequenceMeta;

    /**
     * @var SequenceFactory
     * @since 2.0.0
     */
    protected $sequenceFactory;

    /**
     * @param ResourceSequenceMeta $resourceSequenceMeta
     * @param SequenceFactory $sequenceFactory
     * @since 2.0.0
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
     * @return \Magento\Framework\DB\Sequence\SequenceInterface
     * @since 2.0.0
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
