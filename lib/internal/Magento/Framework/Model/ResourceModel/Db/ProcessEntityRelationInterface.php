<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\ResourceModel\Db;

/**
 * Class ProcessEntityRelationInterface
 */
interface ProcessEntityRelationInterface
{
    /**
     * @param string $entityType
     * @param object $entity
     * @return object
     */
    public function execute($entityType, $entity);
}
