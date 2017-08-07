<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\EntitySnapshot\AttributeProvider;

/**
 * Class EntitySnapshot
 * @since 2.1.0
 */
class EntitySnapshot
{
    /**
     * Array of snapshots of entities data
     *
     * @var array
     * @since 2.1.0
     */
    protected $snapshotData = [];

    /**
     * @var MetadataPool
     * @since 2.1.0
     */
    protected $metadataPool;

    /**
     * @var AttributeProvider
     * @since 2.1.0
     */
    protected $attributeProvider;

    /**
     * @param MetadataPool $metadataPool
     * @param AttributeProvider $attributeProvider
     * @since 2.1.0
     */
    public function __construct(
        MetadataPool $metadataPool,
        AttributeProvider $attributeProvider
    ) {
        $this->metadataPool = $metadataPool;
        $this->attributeProvider = $attributeProvider;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return void
     * @since 2.1.0
     */
    public function registerSnapshot($entityType, $entity)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $hydrator = $this->metadataPool->getHydrator($entityType);
        $entityData = $hydrator->extract($entity);
        $attributes = $this->attributeProvider->getAttributes($entityType);
        $this->snapshotData[$entityType][$entityData[$metadata->getIdentifierField()]]
            = array_intersect_key($entityData, $attributes);
    }

    /**
     * Check is current entity has changes, by comparing current object state with stored snapshot
     *
     * @param string $entityType
     * @param object $entity
     * @return bool
     * @since 2.1.0
     */
    public function isModified($entityType, $entity)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $hydrator = $this->metadataPool->getHydrator($entityType);
        $entityData = $hydrator->extract($entity);
        if (empty($entityData[$metadata->getIdentifierField()])) {
            return true;
        }
        $identifier = $entityData[$metadata->getIdentifierField()];
        if (!isset($this->snapshotData[$entityType][$identifier])) {
            return true;
        }
        foreach ($this->snapshotData[$entityType][$identifier] as $field => $value) {
            if (isset($entityData[$field]) && $entityData[$field] != $value) {
                return true;
            }
        }
        return false;
    }
}
