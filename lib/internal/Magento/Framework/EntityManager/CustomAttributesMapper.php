<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager;

/**
 * Class CustomAttributesMapper
 */
class CustomAttributesMapper implements MapperInterface
{
    /**
     * @var MapperInterface
     */
    private $mapper;

    /**
     * CustomAttributesMapper constructor.
     *
     * @param MapperInterface $mapper
     */
    public function __construct(MapperInterface $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * {@inheritdoc}
     */
    public function entityToDatabase($entityType, $data)
    {
        return $this->mapper->entityToDatabase($entityType, $data);
    }

    /**
     * {@inheritdoc}
     * @deprecated
     */
    public function databaseToEntity($entityType, $data)
    {
        return $this->mapper->databaseToEntity($entityType, $data);
    }
}
