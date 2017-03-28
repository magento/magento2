<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Operation\Delete;

use Magento\Framework\EntityManager\TypeResolver;
use Magento\Framework\EntityManager\Operation\ExtensionPool;

/**
 * Class DeleteExtensions
 */
class DeleteExtensions
{
    /**
     * @var TypeResolver
     */
    private $typeResolver;

    /**
     * @var ExtensionPool
     */
    private $extensionPool;

    /**
     * @param TypeResolver $typeResolver
     * @param ExtensionPool $extensionPool
     */
    public function __construct(
        TypeResolver $typeResolver,
        ExtensionPool $extensionPool
    ) {
        $this->typeResolver = $typeResolver;
        $this->extensionPool = $extensionPool;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     */
    public function execute($entity, $arguments = [])
    {
        $entityType = $this->typeResolver->resolve($entity);
        $actions = $this->extensionPool->getActions($entityType, 'delete');
        foreach ($actions as $action) {
            $action->execute($entity, $arguments);
        }
        return $entity;
    }
}
