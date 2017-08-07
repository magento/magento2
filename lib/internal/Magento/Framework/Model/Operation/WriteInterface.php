<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Operation;

/**
 * Interface WriteInterface
 * @since 2.1.0
 */
interface WriteInterface
{
    /**
     * @param string $entityType
     * @param object $entity
     * @return object|bool
     * @since 2.1.0
     */
    public function execute($entityType, $entity);
}
