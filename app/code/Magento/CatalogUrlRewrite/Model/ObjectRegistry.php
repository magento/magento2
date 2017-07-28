<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model;

/**
 * Class \Magento\CatalogUrlRewrite\Model\ObjectRegistry
 *
 * @since 2.0.0
 */
class ObjectRegistry
{
    /**
     * Key is id of entity, value is entity
     *
     * @var \Magento\Framework\DataObject[]
     * @since 2.0.0
     */
    protected $entitiesMap;

    /**
     * @param \Magento\Framework\DataObject[] $entities
     * @since 2.0.0
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
     * @return \Magento\Framework\DataObject|null
     * @since 2.0.0
     */
    public function get($entityId)
    {
        return isset($this->entitiesMap[$entityId]) ? $this->entitiesMap[$entityId] : null;
    }

    /**
     * @return \Magento\Framework\DataObject[]
     * @since 2.0.0
     */
    public function getList()
    {
        return $this->entitiesMap;
    }
}
