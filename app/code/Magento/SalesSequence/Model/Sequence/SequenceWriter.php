<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\SalesSequence\Model\Sequence;

use Magento\SalesSequence\Model\Resource\Sequence\Meta as ResourceSequenceMeta;
use Magento\SalesSequence\Model\SequenceFactory;
use Magento\Framework\DB\Ddl\Sequence;

/**
 * Class SequenceWriter
 */
class SequenceWriter
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
     * @var ProfileFactory
     */
    protected $profileFactory;

    /**
     * @var MetaFactory
     */
    protected $metaFactory;

    /**
     * @var Sequence
     */
    protected $sequence;

    /**
     * @param ResourceSequenceMeta $resourceSequenceMeta
     * @param SequenceFactory $sequenceFactory
     * @param MetaFactory $metaFactory
     * @param ProfileFactory $profileFactory
     * @param Sequence $sequence
     */
    public function __construct(
        ResourceSequenceMeta $resourceSequenceMeta,
        SequenceFactory $sequenceFactory,
        MetaFactory $metaFactory,
        ProfileFactory $profileFactory,
        Sequence $sequence
    ) {
        $this->resourceSequenceMeta = $resourceSequenceMeta;
        $this->sequenceFactory = $sequenceFactory;
        $this->metaFactory = $metaFactory;
        $this->profileFactory = $profileFactory;
        $this->sequence = $sequence;
    }

    /**
     * Create sequence for entity in store scope
     *
     * @param string $entityType
     * @param int $storeId
     * @param string $prefix
     * @param string $suffix
     * @param int $startValue
     * @param int $step
     * @param int $maxValue
     * @param int $warningValue
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function addSequence($entityType, $storeId, $prefix, $suffix, $startValue, $step, $maxValue, $warningValue)
    {
        $meta = $this->resourceSequenceMeta->loadBy($entityType, $storeId);
        if ($meta->getId()) {
            throw new \Magento\Framework\Exception\AlreadyExistsException(
                __('Sequence with this metadata already exists')
            );
        }

        $meta = $this->metaFactory->create()->setData([
            'entity_type' => $entityType,
            'store_id' => $storeId,
            'sequence_table' => $this->generateTableName($entityType, $storeId)
        ]);

        $profile = $this->profileFactory->create()->setData([
            'prefix' => $prefix,
            'suffix' => $suffix,
            'start_value' => $startValue,
            'step' => $step,
            'max_value' => $maxValue,
            'warning_value' => $warningValue
        ]);
        $meta->setData('active_profile', $profile);
        $meta->save();
        $this->resourceSequenceMeta->getReadConnection()->query(
            $this->sequence->createSequence(
                $meta->getSequenceTable(),
                $meta->getData('active_profile')->getStartValue()
            )
        );
    }

    /**
     * Generates sequence table name
     *
     * @param $entityType
     * @param $storeId
     * @return string
     */
    protected function generateTableName($entityType, $storeId)
    {
        return sprintf('sequence_%s_%s', $entityType, $storeId);
    }
}