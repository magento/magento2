<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Operation\Write\Update;

use Magento\Framework\EntityManager\Operation\ExtensionPool;

/**
 * Class UpdateExtensions
 */
class UpdateExtensions
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entityType, $entity, $arguments = [])
    {
        $actions = $this->extensionPool->getActions($entityType, 'update');
        foreach ($actions as $action) {
            $entity = $action->execute($entityType, $entity);
        }
        return $entity;
    }
}
