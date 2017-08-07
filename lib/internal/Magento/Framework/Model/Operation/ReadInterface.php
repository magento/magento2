<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Operation;

/**
 * Interface ReadInterface
 * @since 2.1.0
 */
interface ReadInterface
{
    /**
     * @param string $entityType
     * @param object $entity
     * @param string $identifier
     * @return object
     * @since 2.1.0
     */
    public function execute($entityType, $entity, $identifier);
}
