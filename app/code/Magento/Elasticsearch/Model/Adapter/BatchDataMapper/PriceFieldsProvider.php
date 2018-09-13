<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\BatchDataMapper;

use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\ResourceModel\Index;
use Magento\Store\Model\StoreManagerInterface;
use Magento\AdvancedSearch\Model\Adapter\DataMapper\AdditionalFieldsProviderInterface;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider;

/**
 * Provide data mapping for price fields
 */
class PriceFieldsProvider implements AdditionalFieldsProviderInterface
{
    /**
     * @var Index
     */
    private $resourceIndex;

    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var FieldMapperInterface
     */
    private $fieldMapper;

    /**
     * @param Index $resourceIndex
     * @param DataProvider $dataProvider
     * @param StoreManagerInterface $storeManager
     * @param FieldMapperInterface $fieldMapper
     */
    public function __construct(
        Index $resourceIndex,
        DataProvider $dataProvider,
        StoreManagerInterface $storeManager,
        FieldMapperInterface $fieldMapper
    ) {
        $this->resourceIndex = $resourceIndex;
        $this->dataProvider = $dataProvider;
        $this->storeManager = $storeManager;
        $this->fieldMapper = $fieldMapper;
    }

    /**
     * {@inheritdoc}
     */
    public function getFields(array $productIds, $storeId)
    {
        $priceData = $this->dataProvider->getSearchableAttribute('price')
            ? $this->resourceIndex->getPriceIndexData($productIds, $storeId)
            : [];

        $fields = [];
        foreach ($productIds as $productId) {
            $fields[$productId] = $this->getProductPriceData($productId, $priceData);
        }

        return $fields;
    }

    /**
     * Prepare price index for product
     *
     * @param int $productId
     * @param array $priceIndexData
     * @return array
     */
    private function getProductPriceData($productId, array $priceIndexData)
    {
        $result = [];
        if (array_key_exists($productId, $priceIndexData)) {
            $productPriceIndexData = $priceIndexData[$productId];
            foreach ($productPriceIndexData as $customerGroupId => $price) {
                $fieldName = $this->fieldMapper->getFieldName(
                    'price',
                    ['customerGroupId' => $customerGroupId]
                );
                $result[$fieldName] = sprintf('%F', $price);
            }
        }

        return $result;
    }
}
