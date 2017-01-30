<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

use Magento\Framework\Model\EntityRegistry;
use Magento\Framework\EntityManager\EntityManager;

/**
 * Class CatalogRegistry
 */
class CatalogRegistry
{
    /**
     * @var EntityRegistry
     */
    protected $entityRegistry;

    /**
     * CatalogRegistry constructor.
     *
     * @param EntityRegistry $entityRegistry
     */
    public function __construct(
        EntityRegistry $entityRegistry
    ) {
        $this->entityRegistry = $entityRegistry;
    }

    /**
     * @param EntityManager $subject
     * @param \Closure $proceed
     * @param string $entityType
     * @param object $entity
     * @param string $identifier
     * @return null|object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
