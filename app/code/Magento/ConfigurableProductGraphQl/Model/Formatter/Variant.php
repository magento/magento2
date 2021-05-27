<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Formatter;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Formatter for configurable product variant
 */
class Variant
{
    /**
     * Format selected variant of configurable product based on selected options
     *
     * @param array $options
     * @param array $selectedOptions
     * @param array $variants
     * @return array|null
     * @throws GraphQlInputException
     */
    public function format(array $options, array $selectedOptions, array $variants): ?array
    {
        $variant = null;
        $productIds = array_keys($variants);

        foreach ($selectedOptions as $attributeId => $selectedValue) {
            if (!isset($options[$attributeId][$selectedValue])) {
                throw new GraphQlInputException(__('configurableOptionValueUids values are incorrect'));
            }

            $productIds = array_intersect($productIds, $options[$attributeId][$selectedValue]);
        }

        if (count($productIds) === 1) {
            $variantProduct = $variants[array_pop($productIds)];
            $variant = $variantProduct->getData();
            $variant['url_path'] = $variantProduct->getProductUrl();
            $variant['model'] = $variantProduct;
        }

        return $variant;
    }
}
