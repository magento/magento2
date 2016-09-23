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
     * @param MapperInterface|null $mapper
     */
    public function __construct(MapperInterface $mapper = null)
    {
        $this->mapper = $mapper;
    }

    /**
     * {@inheritdoc}
     * @deprecated
     */
    public function entityToDatabase($entityType, $data)
    {
        return $this->mapper !== null ? $this->mapper->entityToDatabase($entityType, $data) : $data;
    }

    /**
     * {@inheritdoc}
     * @deprecated
     */
    public function databaseToEntity($entityType, $data)
    {
        return $this->mapper !== null ? $this->mapper->databaseToEntity($entityType, $data) : $data;
    }
}
