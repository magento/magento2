<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager\Operation;

/**
 * Interface ExtensionInterface
 */
interface ExtensionInterface
{
    /**
     * Perform action on relation/extension attribute
     *
     * @param object $entity
     * @param array $arguments
     * @return object|bool
     */
    public function execute($entity, $arguments = []);
}
