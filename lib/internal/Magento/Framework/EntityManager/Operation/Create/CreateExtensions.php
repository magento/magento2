<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager\Operation\Create;

use Magento\Framework\EntityManager\TypeResolver;
use Magento\Framework\EntityManager\Operation\ExtensionPool;

/**
 * Class CreateExtensions
 */
class CreateExtensions
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
        $actions = $this->extensionPool->getActions($entityType, 'create');
        foreach ($actions as $action) {
            $entity = $action->execute($entity, $arguments);
        }
        return $entity;
    }
}
