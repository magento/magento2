<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Operation;

/**
 * Interface AttributeInterface
 * @since 2.1.0
 */
interface AttributeInterface
{
    /**
     * @param string $entityType
     * @param array $entityData
     * @param array $arguments
     * @return array
     * @since 2.1.0
     */
    public function execute($entityType, $entityData, $arguments = []);
}
