<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\CategoryProduct;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\FormatterInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\SearchResultInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory;

/**
 * Flaternize custom attributes
 */
class CustomAttributesFlatternizer
{
    /**
     * Graphql is waiting for flat array
     *
     * @param array $categoryData
     * @return array
     */
    public function flaternize(array $categoryData)
    {
        if (!isset($categoryData['custom_attributes'])) {
            return $categoryData;
        }

        foreach ($categoryData['custom_attributes'] as $attributeData) {
            $categoryData[$attributeData['attribute_code']] = $attributeData['value'];
        }

        unset($categoryData['custom_attributes']);

        return $categoryData;
    }
}
