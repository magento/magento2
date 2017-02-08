<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager;

use Magento\Framework\EntityManager\MapperPool;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\EntityManager\TypeResolver;

/**
 * Class Hydrator
 */
class Hydrator implements HydratorInterface
{
    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var TypeResolver
     */
    private $typeResolver;

    /**
     * @var MapperPool
     */
    private $mapperPool;

    /**
     * @param DataObjectProcessor $dataObjectProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param TypeResolver $typeResolver
     * @param MapperPool $mapperPool
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
