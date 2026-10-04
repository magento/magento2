<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\EntityManager\Operation;

/**
 * Interface AttributeInterface
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
