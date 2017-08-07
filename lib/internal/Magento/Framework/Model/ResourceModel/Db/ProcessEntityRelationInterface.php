<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\ResourceModel\Db;

/**
 * Class ProcessEntityRelationInterface
 * @since 2.1.0
 */
interface ProcessEntityRelationInterface
{
    /**
     * @param string $entityType
     * @param object $entity
     * @return object
     * @since 2.1.0
     */
    public function execute($entityType, $entity);
}
