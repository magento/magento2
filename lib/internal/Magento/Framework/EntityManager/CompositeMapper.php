<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\EntityManager;

/**
 * Class CompositeMapper
 */
class CompositeMapper implements MapperInterface
{
    /**
     * @var MapperInterface[]
     */
    private $mappers;

    /**
     * @param MapperInterface[] $mappers
     */
    public function __construct(
        $mappers
    ) {
        $this->mappers = $mappers;
    }

    /**
     * {@inheritdoc}
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
     */
    public function databaseToEntity($entityType, $data)
    {
        foreach ($this->mappers as $mapper) {
            $data = $mapper->databaseToEntity($entityType, $data);
        }
        return $data;
    }
}
