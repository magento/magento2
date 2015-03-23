<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Model\Sequence;

use Magento\Sales\Model\AbstractModel;
use Magento\SalesSequence\Model\Resource\Sequence\Meta as ResourceSequenceMeta;
use Magento\SalesSequence\Model\SequenceFactory;

/**
 * Class SequenceManager
 */
class SequenceManager
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
     * @param AbstractModel $entity
     * @param int $storeId
     * @return \Magento\Framework\DB\Sequence\SequenceInterface
     */
    public function getSequence(AbstractModel $entity, $storeId)
    {
        return $this->sequenceFactory->create(
            [
                'meta' => $this->resourceSequenceMeta->loadByEntityTypeAndStore(
                    $entity->getEntityType(),
                    $storeId
                )
            ]
        );
    }
}