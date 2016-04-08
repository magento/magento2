<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Operation;

/**
 * Interface ExtensionInterface
 */
interface ExtensionInterface
{
    /**
     * @param string $entityType
     * @param object $entity
     * @param array $arguments
     * @return object|bool
     */
    public function execute($entityType, $entity, $arguments = []);
}
