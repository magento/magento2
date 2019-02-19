<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product\Type;

/**
 * @api
 * @since 100.0.2
 */
class VariationMatrix
{
    /**
     * Generate matrix of variation
     *
     * @param array $usedProductAttributes
     * @return array
     */
    public function getVariations($usedProductAttributes)
    {
        $variationalAttributes = $this->combineVariationalAttributes($usedProductAttributes);

        $attributesCount = count($variationalAttributes);
        if ($attributesCount === 0) {
            return [];
        }

        $variations = [];
        $currentVariation = array_fill(0, $attributesCount, 0);
        $variationalAttributes = array_reverse($variationalAttributes);
        $lastAttribute = $attributesCount - 1;
        do {
            $this->incrementVariationalIndex($attributesCount, $variationalAttributes, $currentVariation);
            if ($currentVariation[$lastAttribute] >= count($variationalAttributes[$lastAttribute]['values'])) {
                break;
            }

            $filledVariation = [];
            for ($attributeIndex = $attributesCount; $attributeIndex--;) {
                $currentAttribute = $variationalAttributes[$attributeIndex];
                $currentVariationValue = $currentVariation[$attributeIndex];
                $filledVariation[$currentAttribute['id']] = $currentAttribute['values'][$currentVariationValue];
            }

            $variations[] = $filledVariation;
            $currentVariation[0]++;
        } while (true);

        return $variations;
    }

    /**
     * Combine variational attributes
     *
     * @param array $usedProductAttributes
     * @return array
     */
    private function combineVariationalAttributes($usedProductAttributes)
    {
        $variationalAttributes = [];
        foreach ($usedProductAttributes as $attribute) {
            $options = [];
            foreach ($attribute['options'] as $valueInfo) {
                foreach ($attribute['values'] as $priceData) {
                    if (isset($priceData['value_index']) && $priceData['value_index'] == $valueInfo['value']
                        && (!isset($priceData['include']) || $priceData['include'])
                    ) {
                        $valueInfo['price'] = $priceData;
                        $options[] = $valueInfo;
                    }
                }
            }
            $variationalAttributes[] = ['id' => $attribute['attribute_id'], 'values' => $options];
        }
        return $variationalAttributes;
    }

    /**
     * Increment index in variation with shift if overflow
     *
     * @param int $attributesCount
     * @param array $variationalAttributes
     * @param array $currentVariation
     * @return void
     */
    private function incrementVariationalIndex($attributesCount, $variationalAttributes, &$currentVariation)
    {
        for ($attributeIndex = 0; $attributeIndex < $attributesCount - 1; ++$attributeIndex) {
            if ($currentVariation[$attributeIndex] >= count($variationalAttributes[$attributeIndex]['values'])) {
                $currentVariation[$attributeIndex] = 0;
                ++$currentVariation[$attributeIndex + 1];
            }
        }
    }
}
