<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\SalesSequence\Model\Sequence;

use Magento\SalesSequence\Model\Resource\Sequence\Meta as ResourceSequenceMeta;
use Magento\SalesSequence\Model\SequenceFactory;

/**
 * Class SequenceReader
 */
class SequenceReader
{
    /**
     * @var ResourceSequenceMeta
     */
    protected $resourceSequenceMeta;

    /**
     * @var SequenceFactory
     */
    protected $sequenceFactory;

    public function __construct(
        ResourceSequenceMeta $resourceSequenceMeta,
        SequenceFactory $sequenceFactory
    ) {
        $this->resourceSequenceMeta = $resourceSequenceMeta;
        $this->sequenceFactory = $sequenceFactory;
    }

    /**
     * @param \Magento\Sales\Model\AbstractModel $entity
     * @return \Magento\SalesSequence\Model\Sequence
     */
    public function getSequence(\Magento\Sales\Model\AbstractModel $entity)
    {
        return $this->sequenceFactory->create(['meta' => $this->resourceSequenceMeta->loadBy(
            $entity->getEntityType(),
            $entity->getStore()->getId()
        )]);
    }
}