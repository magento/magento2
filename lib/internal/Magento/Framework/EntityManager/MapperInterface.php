<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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
