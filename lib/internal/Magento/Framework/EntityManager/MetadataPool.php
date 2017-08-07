<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager;

use Magento\Framework\EntityManager\Sequence\SequenceFactory;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class MetadataPool
 *
 * @api
 * @since 2.1.0
 */
class MetadataPool
{
    /**
     * @var ObjectManagerInterface
     * @since 2.1.0
     */
    protected $objectManager;

    /**
     * @var array
     * @since 2.1.0
     */
    protected $metadata;

    /**
     * @var \Magento\Framework\EntityManager\EntityMetadata[]
     * @since 2.1.0
     */
    protected $registry;

    /**
     * @var SequenceFactory
     * @since 2.1.0
     */
    protected $sequenceFactory;

    /**
     * MetadataPool constructor.
     * @param ObjectManagerInterface $objectManager
     * @param SequenceFactory $sequenceFactory
     * @param array $metadata
     * @since 2.1.0
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
     * @param string $entityType
     * @return EntityMetadataInterface
     * @since 2.1.0
     */
    private function createMetadata($entityType)
    {
        //@todo: use ID as default if , check is type has EAV attributes
        $connectionName = isset($this->metadata[$entityType]['connectionName'])
            ? $this->metadata[$entityType]['connectionName']
            : 'default';
        $eavEntityType = isset($this->metadata[$entityType]['eavEntityType'])
            ? $this->metadata[$entityType]['eavEntityType']
            : null;
        $entityContext = isset($this->metadata[$entityType]['entityContext'])
            ? $this->metadata[$entityType]['entityContext']
            : [];
        return $this->objectManager->create(
            EntityMetadataInterface::class,
            [
                'entityTableName' => $this->metadata[$entityType]['entityTableName'],
                'eavEntityType' => $eavEntityType,
                'connectionName' => $connectionName,
                'identifierField' => $this->metadata[$entityType]['identifierField'],
                'sequence' => $this->sequenceFactory->create($entityType, $this->metadata),
                'entityContext' => $entityContext
            ]
        );
    }

    /**
     * @param string $entityType
     * @return EntityMetadataInterface
     * @throws \Exception
     * @since 2.1.0
     */
    public function getMetadata($entityType)
    {
        if (!isset($this->metadata[$entityType])) {
            throw new \Exception(sprintf('Unknown entity type: %s requested', $entityType));
        }
        if (!isset($this->registry[$entityType])) {
            $this->registry[$entityType] = $this->createMetadata($entityType);
        }
        return $this->registry[$entityType];
    }

    /**
     * @param string $entityType
     * @return HydratorInterface
     * @deprecated 2.1.0
     * @since 2.1.0
     */
    public function getHydrator($entityType)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager->get(HydratorPool::class)->getHydrator($entityType);
    }

    /**
     * Check if entity type configuration was set to metadata
     *
     * @param string $entityType
     * @return bool
     * @since 2.1.0
     */
    public function hasConfiguration($entityType)
    {
        return isset($this->metadata[$entityType]);
    }
}
