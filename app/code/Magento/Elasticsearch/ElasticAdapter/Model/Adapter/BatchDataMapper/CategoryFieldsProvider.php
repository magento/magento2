<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\ElasticAdapter\Model\Adapter\BatchDataMapper;

use Magento\AdvancedSearch\Model\Adapter\DataMapper\AdditionalFieldsProviderInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface;
use Magento\Elasticsearch\Model\ResourceModel\Index;

/**
 * Provide data mapping for categories fields
 * @deprecated Elasticsearch is no longer supported by Adobe
 * @see this class will be responsible for ES only
 */
class CategoryFieldsProvider implements AdditionalFieldsProviderInterface
{
    /**
     * @var Index
     */
    private $resourceIndex;

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
     * @param AttributeProvider $attributeAdapterProvider
     * @param ResolverInterface $fieldNameResolver
     */
    public function __construct(
        Index $resourceIndex,
        AttributeProvider $attributeAdapterProvider,
        ResolverInterface $fieldNameResolver
    ) {
        $this->resourceIndex = $resourceIndex;
        $this->attributeAdapterProvider = $attributeAdapterProvider;
        $this->fieldNameResolver = $fieldNameResolver;
    }

    /**
     * @inheritdoc
     */
    public function getFields(array $productIds, $storeId)
    {
        $categoryData = $this->resourceIndex->getFullCategoryProductIndexData($storeId, $productIds);

        $fields = [];
        foreach ($productIds as $productId) {
            $fields[$productId] = $this->getProductCategoryData($productId, $categoryData);
        }

        return $fields;
    }

    /**
     * Prepare category index data for product
     *
     * @param int $productId
     * @param array $categoryIndexData
     * @return array
     */
    private function getProductCategoryData($productId, array $categoryIndexData)
    {
        $result = [];

        if (array_key_exists($productId, $categoryIndexData)) {
            $indexData = $categoryIndexData[$productId];
            $categoryIds = array_column($indexData, 'id');

            if (count($categoryIds)) {
                $result = ['category_ids' => $categoryIds];
                $positionAttribute = $this->attributeAdapterProvider->getByAttributeCode('position');
                $categoryNameAttribute = $this->attributeAdapterProvider->getByAttributeCode('category_name');
                foreach ($indexData as $data) {
                    $categoryPositionKey = $this->fieldNameResolver->getFieldName(
                        $positionAttribute,
                        ['categoryId' => $data['id']]
                    );
                    $categoryNameKey = $this->fieldNameResolver->getFieldName(
                        $categoryNameAttribute,
                        ['categoryId' => $data['id']]
                    );
                    $result[$categoryPositionKey] = $data['position'];
                    $result[$categoryNameKey] = $data['name'];
                }
            }
        }

        return $result;
    }
}
