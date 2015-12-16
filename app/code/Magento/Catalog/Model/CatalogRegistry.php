<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

use Magento\Framework\Model\EntityRegistry;
use Magento\Framework\Model\EntityManager;

/**
 * Class CatalogRegistry
 */
class CatalogRegistry
{
    protected $entityRegistry;

    public function __construct(
        EntityRegistry $entityRegistry
    ) {
        $this->entityRegistry = $entityRegistry;
    }

    /**
     * @param EntityManager $subject
     * @param \Closure $proceed
     * @param $entityType
     * @param $entity
     * @param $identifier
     * @return null|object
     */
    public function aroundLoad(EntityManager $subject, \Closure $proceed, $entityType, $entity, $identifier)
    {
        $object = $this->entityRegistry->retrieve($entityType, $identifier);
        if (!$object) {
            $object = $proceed($entityType, $entity, $identifier);
            $this->entityRegistry->register($entityType, $identifier, $object);
        }
        return $object;
    }
}