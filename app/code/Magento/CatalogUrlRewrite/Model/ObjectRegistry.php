<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model;

/**
 * Class ObjectRegistry
 */
class ObjectRegistry
{
    /**
     * Key is id of entity, value is entity
     *
     * @var \Magento\Framework\DataObject[]
     */
    protected $entitiesMap;

    /**
     * @param \Magento\Framework\DataObject[] $entities
     */
    public function __construct($entities)
    {
        $this->entitiesMap = [];
        foreach ($entities as $entity) {
            $this->entitiesMap[$entity->getId()] = $entity;
        }
    }

    /**
     * Get Entity
     *
     * @param int $entityId
     * @return \Magento\Framework\DataObject|null
     */
    public function get($entityId)
    {
        return $this->entitiesMap[$entityId] ?? null;
    }

    /**
     * List Entities
     *
     * @return \Magento\Framework\DataObject[]
     */
    public function getList()
    {
        return $this->entitiesMap;
    }
}
