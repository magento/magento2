<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Formatter;

use Magento\CatalogInventory\Model\StockRegistry;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\ConfigurableProductGraphQl\Model\Options\SelectionUidFormatter;

/**
 * Formatter for configurable product option values
 */
class OptionValue
{
    /**
     * @var SelectionUidFormatter
     */
    private $selectionUidFormatter;

    /**
     * @var StockRegistry
     */
    private $stockRegistry;

    /**
     * @param SelectionUidFormatter $selectionUidFormatter
     * @param StockRegistry $stockRegistry
     */
    public function __construct(
        SelectionUidFormatter $selectionUidFormatter,
        StockRegistry $stockRegistry
    ) {
        $this->selectionUidFormatter = $selectionUidFormatter;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Format configurable product option values according to the GraphQL schema
     *
     * @param array $optionValue
     * @param Attribute $attribute
     * @param array $optionIds
     * @return array
     */
    public function format(array $optionValue, Attribute $attribute, array $optionIds): array
    {
        $valueIndex = (int)$optionValue['value_index'];
        $attributeId = (int)$attribute->getAttributeId();

        return [
            'uid' => $this->selectionUidFormatter->encode(
                $attributeId,
                $valueIndex
            ),
            'is_available' => $this->getIsAvailable($optionIds[$valueIndex] ?? []),
            'is_use_default' => (bool)$attribute->getIsUseDefault(),
            'label' => $optionValue['label'],
            'value_index' => $optionValue['value_index']
        ];
    }

    /**
     * Get is variants available
     *
     * @param array $variantIds
     * @return bool
     */
    private function getIsAvailable(array $variantIds): bool
    {
        foreach ($variantIds as $variantId) {
            if ($this->stockRegistry->getProductStockStatus($variantId)) {
                return true;
            }
        }

        return false;
    }
}
