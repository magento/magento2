<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Operation;

/**
 * Interface WriteInterface
 */
interface WriteInterface
{
    /**
     * @param string $entityType
     * @param object $entity
     * @return object|bool
     */
    public function execute($entityType, $entity);
}
