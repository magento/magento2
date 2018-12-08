<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider;

/**
 * Flatten custom attributes
 */
class CustomAttributesFlattener
{
    /**
     * Flatten custom attributes within its enclosing array to normalize key-value pairs.
     *
     * @param array $categoryData
     * @return array
     */
    public function flatten(array $categoryData) : array
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
