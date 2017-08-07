<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager;

/**
 * Class CompositeMapper
 * @since 2.1.0
 */
class CompositeMapper implements MapperInterface
{
    /**
     * @var MapperInterface[]
     * @since 2.1.0
     */
    private $mappers;

    /**
     * @param MapperInterface[] $mappers
     * @since 2.1.0
     */
    public function __construct(
        $mappers
    ) {
        $this->mappers = $mappers;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function entityToDatabase($entityType, $data)
    {
        foreach ($this->mappers as $mapper) {
            $data = $mapper->entityToDatabase($entityType, $data);
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function databaseToEntity($entityType, $data)
    {
        foreach ($this->mappers as $mapper) {
            $data = $mapper->databaseToEntity($entityType, $data);
        }
        return $data;
    }
}
