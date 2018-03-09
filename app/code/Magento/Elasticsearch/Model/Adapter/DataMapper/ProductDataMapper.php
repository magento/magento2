<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\DataMapper;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Elasticsearch\Model\Adapter\DataMapperInterface;
use Magento\Elasticsearch\Elasticsearch5\Model\Adapter\DataMapper\ProductDataMapper as ElasticSearch5ProductDataMapper;

/**
 * @deprecated 100.2.0
 * @see \Magento\Elasticsearch\Model\Adapter\BatchDataMapperInterface
 */
class ProductDataMapper extends ElasticSearch5ProductDataMapper implements DataMapperInterface
{
    /**
     * Prepare category index data for product
     *
     * @param int $productId
     * @param array $categoryIndexData
     * @return array
     */
    protected function getProductCategoryData($productId, array $categoryIndexData)
    {
        $result = [];
        $categoryIds = [];

        if (array_key_exists($productId, $categoryIndexData)) {
            $indexData = $categoryIndexData[$productId];
            $result = $indexData;
        }

        if (array_key_exists($productId, $categoryIndexData)) {
            $indexData = $categoryIndexData[$productId];
            foreach ($indexData as $categoryData) {
                $categoryIds[] = $categoryData['id'];
            }
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
