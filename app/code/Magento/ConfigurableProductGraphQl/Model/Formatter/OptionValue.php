<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Formatter;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\ConfigurableProductGraphQl\Model\Options\SelectionUidFormatter;
use Magento\SwatchesGraphQl\Model\Resolver\Product\Options\DataProvider\SwatchDataProvider;

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
     * @var SwatchDataProvider
     */
    private $swatchDataProvider;

    /**
     * @param SelectionUidFormatter $selectionUidFormatter
     * @param SwatchDataProvider $swatchDataProvider
     */
    public function __construct(
        SelectionUidFormatter $selectionUidFormatter,
        SwatchDataProvider $swatchDataProvider
    ) {
        $this->selectionUidFormatter = $selectionUidFormatter;
        $this->swatchDataProvider = $swatchDataProvider;
    }

    /**
     * Format configurable product option values according to the GraphQL schema
     *
     * @param array $optionValue
     * @param Attribute $attribute
     * @return array
     */
    public function format(array $optionValue, Attribute $attribute): array
    {
        $valueIndex = (int)$optionValue['value_index'];
        $attributeId = (int)$attribute->getAttributeId();

        return [
            'uid' => $this->selectionUidFormatter->encode(
                $attributeId,
                $valueIndex
            ),
            'is_available' => true,
            'is_default' => (bool)$attribute->getIsUseDefault(),
            'label' => $optionValue['label'],
            'swatch' => $this->swatchDataProvider->getData($optionValue['value_index'])
        ];
    }
}
