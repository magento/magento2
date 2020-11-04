<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SearchStorefrontElasticsearch\Model\Adapter\BatchDataMapper;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\SearchStorefrontElasticsearch\Model\Adapter\BatchDataMapperInterface;
use Magento\SearchStorefrontElasticsearch\Model\Config;

/**
 * Map index data to search engine metadata
 */
class DataMapperResolver implements BatchDataMapperInterface
{
    /**
     * @var BatchDataMapperInterface
     */
    private $dataMapperEntity;

    /**
     * @var DataMapperFactory
     */
    private $dataMapperFactory;

    /**
     * @param DataMapperFactory $dataMapperFactory
     */
    public function __construct(DataMapperFactory $dataMapperFactory)
    {
        $this->dataMapperFactory = $dataMapperFactory;
    }

    /**
     * @inheritdoc
     */
    public function map(array $documentData, $storeId, array $context = [])
    {
        $entityType = isset($context['entityType']) ? $context['entityType'] : Config::ELASTICSEARCH_TYPE_DEFAULT;
        return $this->getDataMapper($entityType)->map($documentData, $storeId, $context);
    }

    /**
     * Get instance of data mapper for specified entity type
     *
     * @param string $entityType
     * @return BatchDataMapperInterface
     * @throws NoSuchEntityException
     * @throws ConfigurationMismatchException
     */
    private function getDataMapper($entityType)
    {
        if (!isset($this->dataMapperEntity[$entityType])) {
            $this->dataMapperEntity[$entityType] = $this->dataMapperFactory->create($entityType);
        }

        return $this->dataMapperEntity[$entityType];
    }
}
