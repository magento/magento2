<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Operation\Write\Create;

use Magento\Framework\EntityManager\Operation\ExtensionPool;

/**
 * Class CreateExtensions
 */
class CreateExtensions
{
    /**
     * @var ExtensionPool
     */
    private $extensionPool;

    /**
     * CreateExtensions constructor.
     * @param ExtensionPool $extensionPool
     */
    public function __construct(
        ExtensionPool $extensionPool
    ) {
        $this->extensionPool = $extensionPool;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @param array $arguments
     * @return object
     */
    public function execute($entityType, $entity, $arguments = [])
    {
        $actions = $this->extensionPool->getActions($entityType, 'create');
        foreach ($actions as $action) {
            $entity = $action->execute($entityType, $entity, $arguments);
        }
        return $entity;
    }
}
