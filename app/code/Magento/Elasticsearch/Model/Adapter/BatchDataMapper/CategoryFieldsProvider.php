<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\BatchDataMapper;

use Magento\Elasticsearch\Model\ResourceModel\Index;
use Magento\AdvancedSearch\Model\Adapter\DataMapper\AdditionalFieldsProviderInterface;

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
     * @param Index $resourceIndex
     */
    public function __construct(Index $resourceIndex)
    {
        $this->resourceIndex = $resourceIndex;
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
                    $result['position_category_' . $data['id']] = $data['position'];
                    $result['name_category_' . $data['id']] = $data['name'];
                }
            }
        }

        return $result;
    }
}
