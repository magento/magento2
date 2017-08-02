<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager\Operation\Create;

use Magento\Framework\EntityManager\TypeResolver;
use Magento\Framework\EntityManager\Operation\ExtensionPool;

/**
 * Class CreateExtensions
 * @since 2.1.0
 */
class CreateExtensions
{
    /**
     * @var TypeResolver
     * @since 2.1.0
     */
    private $typeResolver;

    /**
     * @var ExtensionPool
     * @since 2.1.0
     */
    private $extensionPool;

    /**
     * @param TypeResolver $typeResolver
     * @param ExtensionPool $extensionPool
     * @since 2.1.0
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
     * @since 2.1.0
     */
    public function execute($entity, $arguments = [])
    {
        $entityType = $this->typeResolver->resolve($entity);
        $actions = $this->extensionPool->getActions($entityType, 'create');
        foreach ($actions as $action) {
            $entity = $action->execute($entity, $arguments);
        }
        return $entity;
    }
}
