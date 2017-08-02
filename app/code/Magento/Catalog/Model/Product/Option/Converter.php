<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option;

/**
 * Class \Magento\Catalog\Model\Product\Option\Converter
 *
 * @since 2.0.0
 */
class Converter
{
    /**
     * Convert option data to array
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionInterface $option
     * @return array
     * @since 2.0.0
     */
    public function toArray(\Magento\Catalog\Api\Data\ProductCustomOptionInterface $option)
    {
        $optionData = $option->getData();
        $values = $option->getValues();
        $valuesData = [];
        if (!empty($values)) {
            foreach ($values as $key => $value) {
                $valuesData[$key] = $value->getData();
            }
        }
        $optionData['values'] = $valuesData;
        return $optionData;
    }
}
