<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\ConfigurableProduct\Model\Product\Type;

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
            $options = array();
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
            $variationalAttributes[] = array('id' => $attribute['attribute_id'], 'values' => $options);
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
