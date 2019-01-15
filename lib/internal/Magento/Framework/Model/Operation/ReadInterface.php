<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Operation;

/**
 * Interface ReadInterface
 */
interface ReadInterface
{
    /**
     * @param string $entityType
     * @param object $entity
     * @param string $identifier
     * @return object
     */
    public function execute($entityType, $entity, $identifier);
}
