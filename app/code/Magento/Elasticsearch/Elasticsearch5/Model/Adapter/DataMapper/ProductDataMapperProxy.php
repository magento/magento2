<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Elasticsearch5\Model\Adapter\DataMapper;

use Magento\AdvancedSearch\Model\Client\ClientResolver;
use Magento\Elasticsearch\Model\Adapter\DataMapperInterface;

/**
 * Proxy for product data mappers
 */
class ProductDataMapperProxy implements DataMapperInterface
{
    /**
     * @var ClientResolver
     */
    private $clientResolver;

    /**
     * @var DataMapperInterface[]
     */
    private $dataMappers;

    /**
     * CategoryFieldsProviderProxy constructor.
     * @param ClientResolver $clientResolver
     * @param DataMapperInterface[] $dataMappers
     */
    public function __construct(
        ClientResolver $clientResolver,
        array $dataMappers
    ) {
        $this->clientResolver = $clientResolver;
        $this->dataMappers = $dataMappers;
    }

    /**
     * @return DataMapperInterface
     */
    private function getDataMapper()
    {
        return $this->dataMappers[$this->clientResolver->getCurrentEngine()];
    }

    /**
     * @inheritdoc
     */
    public function map($entityId, array $entityIndexData, $storeId, $context = [])
    {
        return $this->getDataMapper()->map($entityId, $entityIndexData, $storeId, $context);
    }
}
