<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\SalesSequence\Model\Sequence;

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
     * @param \Magento\Sales\Model\AbstractModel $entity
     * @return \Magento\Framework\DB\Sequence\SequenceInterface
     */
    public function getSequence(\Magento\Sales\Model\AbstractModel $entity)
    {
        return $this->sequenceFactory->create(
            [
                'meta' => $this->resourceSequenceMeta->loadByEntityTypeAndStore(
                    $entity->getEntityType(),
                    $entity->getStore()->getId()
                )
            ]
        );
    }
}