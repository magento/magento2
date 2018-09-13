<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\BatchDataMapper;

use Magento\Elasticsearch\Model\ResourceModel\Index;
use Magento\AdvancedSearch\Model\Adapter\DataMapper\AdditionalFieldsProviderInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;

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
     * @var FieldMapperInterface
     */
    private $fieldMapper;

    /**
     * @param Index $resourceIndex
     * @param FieldMapperInterface $fieldMapper
     */
    public function __construct(Index $resourceIndex, FieldMapperInterface $fieldMapper)
    {
        $this->resourceIndex = $resourceIndex;
        $this->fieldMapper = $fieldMapper;
    }

    /**
     * {@inheritdoc}
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
                $result = ['category_ids' => implode(' ', $categoryIds)];
                foreach ($indexData as $data) {
                    $categoryPositionKey = $this->fieldMapper->getFieldName('position', ['categoryId' => $data['id']]);
                    $categoryNameKey = $this->fieldMapper->getFieldName('category_name', ['categoryId' => $data['id']]);
                    $result[$categoryPositionKey] = $data['position'];
                    $result[$categoryNameKey] = $data['name'];
                }
            }
        }

        return $result;
    }
}
