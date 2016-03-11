<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Entity;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class MetadataPool
 */
class MetadataPool
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

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
     * MetadataPool constructor.
     * @param ObjectManagerInterface $objectManager
     * @param SequenceFactory $sequenceFactory
     * @param array $metadata
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        SequenceFactory $sequenceFactory,
        array $metadata
    ) {
        $this->objectManager = $objectManager;
        $this->sequenceFactory = $sequenceFactory;
        $this->metadata = $metadata;
    }

    /**
     * @param string $bentityType
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
            $this->registry[$entityType] = $this->objectManager->create(
                EntityMetadata::class,
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
     * @return HydratorInterface
     */
    public function getHydrator($entityType)
    {
        if (!isset($this->metadata[$entityType]['hydrator'])) {
            return $this->objectManager->get(EntityHydrator::class);
        } else {
            return $this->objectManager->get($this->metadata[$entityType]['hydrator']);
        }
    }
}
