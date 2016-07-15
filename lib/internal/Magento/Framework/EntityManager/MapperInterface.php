<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager;

/**
 * MapperInterface
 */
interface MapperInterface
{
    /**
     * Map entity field name to database field name
     *
     * @param string $entityType
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function entityToDatabase($entityType, $data);

    /**
     * Map database field name to entity field name
     *
     * @param string $entityType
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function databaseToEntity($entityType, $data);
}
