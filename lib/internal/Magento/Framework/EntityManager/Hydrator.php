<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager;

use Magento\Framework\EntityManager\MapperPool;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\EntityManager\TypeResolver;

/**
 * Class Hydrator
 * @since 2.1.0
 */
class Hydrator implements HydratorInterface
{
    /**
     * @var DataObjectProcessor
     * @since 2.1.0
     */
    private $dataObjectProcessor;

    /**
     * @var DataObjectHelper
     * @since 2.1.0
     */
    private $dataObjectHelper;

    /**
     * @var TypeResolver
     * @since 2.1.0
     */
    private $typeResolver;

    /**
     * @var MapperPool
     * @since 2.1.0
     */
    private $mapperPool;

    /**
     * @param DataObjectProcessor $dataObjectProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param TypeResolver $typeResolver
     * @param MapperPool $mapperPool
     * @since 2.1.0
     */
    public function __construct(
        DataObjectProcessor $dataObjectProcessor,
        DataObjectHelper $dataObjectHelper,
        TypeResolver $typeResolver,
        MapperPool $mapperPool
    ) {
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->typeResolver = $typeResolver;
        $this->mapperPool = $mapperPool;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function extract($entity)
    {
        $entityType = $this->typeResolver->resolve($entity);
        $data = $this->dataObjectProcessor->buildOutputDataArray($entity, $entityType);
        $mapper = $this->mapperPool->getMapper($entityType);
        return $mapper->entityToDatabase($entityType, $data);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function hydrate($entity, array $data)
    {
        $entityType = $this->typeResolver->resolve($entity);
        $mapper = $this->mapperPool->getMapper($entityType);
        $data = $mapper->databaseToEntity(
            $entityType,
            array_merge($this->extract($entity), $data)
        );
        $this->dataObjectHelper->populateWithArray($entity, $data, $entityType);
        return $entity;
    }
}
