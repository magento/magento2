<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Elasticsearch5\Model\Adapter\BatchDataMapper;

use Magento\Elasticsearch\Model\ResourceModel\Index;
use Magento\AdvancedSearch\Model\Adapter\DataMapper\AdditionalFieldsProviderInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface;

/**
 * Provide data mapping for categories fields
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
     * @param AttributeProvider|null $attributeAdapterProvider
     * @param ResolverInterface|null $fieldNameResolver
     */
    public function __construct(
        Index $resourceIndex,
        AttributeProvider $attributeAdapterProvider = null,
        ResolverInterface $fieldNameResolver = null
    ) {
        $this->resourceIndex = $resourceIndex;
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
