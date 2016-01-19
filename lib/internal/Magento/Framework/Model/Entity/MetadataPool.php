<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Entity;

/**
 * Class MetadataPool
 */
class MetadataPool
{
    /**
     * @var array
     */
    protected $eavMapping;
    /**
     * @var EntityMetadataFactory
     */
    protected $metadataFactory;

    /**
     * @var EntityHydratorFactory
     */
    protected $hydratorFactory;

    /**
     * @var array
     */
    protected $metadata;

    /**
     * @var \Magento\Framework\Model\Entity\EntityMetadata[]
     */
    protected $registry;

    /**
     * @var SequenceFactory
     */
    protected $sequenceFactory;

    /**
     * @param EntityMetadataFactory $metadataFactory
     * @param EntityHydratorFactory $hydratorFactory
     * @param SequenceFactory $sequenceFactory
     * @param array $metadata
     * @param array $eavMapping
     */
    public function __construct(
        EntityMetadataFactory $metadataFactory,
        EntityHydratorFactory $hydratorFactory,
        SequenceFactory $sequenceFactory,
        array $metadata,
        array $eavMapping = []
    ) {
        $this->metadataFactory = $metadataFactory;
        $this->hydratorFactory = $hydratorFactory;
        $this->sequenceFactory = $sequenceFactory;
        $this->metadata = $metadata;
        $this->eavMapping = $eavMapping;
    }

    /**
     * @param string $entityType
     * @return EntityMetadata
     * @throws \Exception
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getMetadata($entityType)
    {
        if (!isset($this->metadata[$entityType])) {
            throw new \Exception('Not enough configuration');
        }
        if (!isset($this->registry[$entityType])) {
            $this->metadata[$entityType]['connectionName'] = 'default';
            $this->registry[$entityType] = $this->metadataFactory->create(
                [
                    'entityTableName' => $this->metadata[$entityType]['entityTableName'],
                    'eavEntityType' => isset($this->metadata[$entityType]['eavEntityType'])
                        ? $this->metadata[$entityType]['eavEntityType']
                        : null,
                        //isset($this->eavMapping[$entityType]) ? $this->eavMapping[$entityType] : null,
                    'connectionName' => $this->metadata[$entityType]['connectionName'],
                    'identifierField' => $this->metadata[$entityType]['identifierField'],
                    'sequence' => $this->sequenceFactory->create($entityType, $this->metadata),
                    'entityContext' => isset($this->metadata[$entityType]['entityContext'])
                        ? $this->metadata[$entityType]['entityContext']
                        : [],
                    'fields' => isset($this->metadata[$entityType]['fields'])
                        ? $this->metadata[$entityType]['fields']
                        : null,
                ]
            );
        }
        return $this->registry[$entityType];
    }

    /**
     * @param string $entityType
     * @return EntityHydrator
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getHydrator($entityType)
    {
        return $this->hydratorFactory->create();
    }
}
