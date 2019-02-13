<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\Adapter\BatchDataMapper;

use Magento\Elasticsearch\Model\ResourceModel\Index;
use Magento\Store\Model\StoreManagerInterface;
use Magento\AdvancedSearch\Model\Adapter\DataMapper\AdditionalFieldsProviderInterface;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface;

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
     * @var AttributeProvider
     */
    private $attributeAdapterProvider;

    /**
     * @var ResolverInterface
     */
    private $fieldNameResolver;

    /**
     * @param Index $resourceIndex
     * @param DataProvider $dataProvider
     * @param StoreManagerInterface $storeManager
     * @param AttributeProvider|null $attributeAdapterProvider
     * @param ResolverInterface|null $fieldNameResolver
     */
    public function __construct(
        Index $resourceIndex,
        DataProvider $dataProvider,
        StoreManagerInterface $storeManager,
        AttributeProvider $attributeAdapterProvider = null,
        ResolverInterface $fieldNameResolver = null
    ) {
        $this->resourceIndex = $resourceIndex;
        $this->dataProvider = $dataProvider;
        $this->storeManager = $storeManager;
        $this->attributeAdapterProvider = $attributeAdapterProvider ?: ObjectManager::getInstance()
            ->get(AttributeProvider::class);
        $this->fieldNameResolver = $fieldNameResolver ?: ObjectManager::getInstance()
            ->get(ResolverInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getFields(array $productIds, $storeId)
    {
        $priceData = $this->dataProvider->getSearchableAttribute('price')
            ? $this->resourceIndex->getPriceIndexData($productIds, $storeId)
            : [];

        $fields = [];
        foreach ($productIds as $productId) {
            $fields[$productId] = $this->getProductPriceData($productId, $storeId, $priceData);
        }

        return $fields;
    }

    /**
     * Prepare price index for product
     *
     * @param int $productId
     * @param int $websiteId
     * @param array $priceIndexData
     * @return array
     */
    private function getProductPriceData($productId, $websiteId, array $priceIndexData)
    {
        $result = [];
        if (array_key_exists($productId, $priceIndexData)) {
            $productPriceIndexData = $priceIndexData[$productId];
            $priceAttribute = $this->attributeAdapterProvider->getByAttributeCode('price');
            foreach ($productPriceIndexData as $customerGroupId => $price) {
                $fieldName = $this->fieldNameResolver->getFieldName(
                    $priceAttribute,
                    ['customerGroupId' => $customerGroupId, 'websiteId' => $websiteId]
                );
                $result[$fieldName] = sprintf('%F', $price);
            }
        }

        return $result;
    }
}
