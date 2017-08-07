<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager;

/**
 * MapperInterface
 * @since 2.1.0
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
     * @since 2.1.0
     */
    public function entityToDatabase($entityType, $data);

    /**
     * Map database field name to entity field name
     *
     * @param string $entityType
     * @param array $data
     * @return array
     * @throws \Exception
     * @since 2.1.0
     */
    public function databaseToEntity($entityType, $data);
}
