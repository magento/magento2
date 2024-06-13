<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Model\ResourceModel\Db;

/**
 * Class ProcessEntityRelationInterface
 */
interface ProcessEntityRelationInterface
{
    /**
     * Process entity relation.
     *
     * @param string $entityType
     * @param object $entity
     * @return object
     */
    public function execute($entityType, $entity);
}
