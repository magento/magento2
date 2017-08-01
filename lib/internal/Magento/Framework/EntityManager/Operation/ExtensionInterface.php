<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager\Operation;

/**
 * Interface ExtensionInterface
 * @since 2.1.0
 */
interface ExtensionInterface
{
    /**
     * Perform action on relation/extension attribute
     *
     * @param object $entity
     * @param array $arguments
     * @return object|bool
     * @since 2.1.0
     */
    public function execute($entity, $arguments = []);
}
