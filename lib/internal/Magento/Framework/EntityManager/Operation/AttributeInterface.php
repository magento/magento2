<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Operation;

/**
 * Interface AttributeInterface
 * @deprecated
 */
interface AttributeInterface
{
    /**
     * @param string $entityType
     * @param array $entityData
     * @param array $arguments
     * @return array
     */
    public function execute($entityType, $entityData, $arguments = []);
}
