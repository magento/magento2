<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model;

class ObjectRegistry
{
    /**
     * Key is id of entity, value is entity
     *
     * @var \Magento\Framework\Object[]
     */
    protected $entitiesMap;

    /**
     * @param \Magento\Framework\Object[] $entities
     */
    public function __construct($entities)
    {
        $this->entitiesMap = [];
        foreach ($entities as $entity) {
            $this->entitiesMap[$entity->getId()] = $entity;
        }
    }

    /**
     * @param int $entityId
     * @return \Magento\Framework\Object|null
     */
    public function get($entityId)
    {
        return isset($this->entitiesMap[$entityId]) ? $this->entitiesMap[$entityId] : null;
    }

    /**
     * @return \Magento\Framework\Object[]
     */
    public function getList()
    {
        return $this->entitiesMap;
    }
}
