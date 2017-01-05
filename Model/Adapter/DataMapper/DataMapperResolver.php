<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\DataMapper;

use Magento\Framework\ObjectManagerInterface;
use Magento\Elasticsearch\Model\Adapter\DataMapperInterface;
use Magento\Elasticsearch\Model\Config;

class DataMapperResolver implements DataMapperInterface
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string[]
     */
    private $dataMappers;

    /**
     * Data Mapper instance
     *
     * @var DataMapperInterface
     */
    private $dataMapperEntity;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string[] $dataMappers
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $dataMappers = []
    ) {
        $this->objectManager = $objectManager;
        $this->dataMappers = $dataMappers;
    }

    /**
     * {@inheritdoc}
     */
    public function map(
        $entityId,
        array $entityIndexData,
        $storeId,
        $context = []
    ) {
        $entityType = isset($context['entityType']) ? $context['entityType'] : Config::ELASTICSEARCH_TYPE_DEFAULT;
        return $this->getEntity($entityType)->map($entityId, $entityIndexData, $storeId, $context);
    }

    /**
     * Get instance of current data mapper
     *
     * @param string $entityType
     * @return DataMapperInterface
     * @throws \Exception
     */
    private function getEntity($entityType = '')
    {
        if (empty($this->dataMapperEntity)) {
            if (empty($entityType)) {
                throw new \Exception(
                    'No entity type given'
                );
            }
            if (!isset($this->dataMappers[$entityType])) {
                throw new \LogicException(
                    'There is no such data mapper: ' . $entityType
                );
            }
            $dataMapperClass = $this->dataMappers[$entityType];
            $this->dataMapperEntity = $this->objectManager->create($dataMapperClass);
            if (!($this->dataMapperEntity instanceof DataMapperInterface)) {
                throw new \InvalidArgumentException(
                    'Data mapper must implement \Magento\Elasticsearch\Model\Adapter\DataMapperInterface'
                );
            }
        }
        return $this->dataMapperEntity;
    }
}
