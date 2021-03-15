<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Options;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\ConfigurableProductGraphQl\Model\Formatter\Option;

/**
 * Retrieve metadata for configurable option selection.
 */
class ConfigurableOptionsMetadata
{
    /**
     * @var Data
     */
    private $configurableProductHelper;

    /**
     * @var Option
     */
    private $configurableOptionsFormatter;

    /**
     * @param Data $configurableProductHelper
     * @param Option $configurableOptionsFormatter
     */
    public function __construct(
        Data $configurableProductHelper,
        Option $configurableOptionsFormatter
    ) {
        $this->configurableProductHelper = $configurableProductHelper;
        $this->configurableOptionsFormatter = $configurableOptionsFormatter;
    }

    /**
     * Load available selections from configurable options and variant.
     *
     * @param ProductInterface $product
     * @param array $options
     * @param array $selectedOptions
     * @return array
     */
    public function getAvailableSelections(ProductInterface $product, array $options, array $selectedOptions): array
    {
        $attributes = $this->getAttributes($product);
        $availableSelections = [];

        foreach ($options as $attributeId => $option) {
            if ($attributeId === 'index' || isset($selectedOptions[$attributeId])) {
                continue;
            }

            $availableSelections[] = $this->configurableOptionsFormatter->format(
                $attributes[$attributeId],
                $options[$attributeId] ?? []
            );
        }

        return $availableSelections;
    }

    /**
     * Retrieve configurable attributes for the product
     *
     * @param ProductInterface $product
     * @return Attribute[]
     */
    private function getAttributes(ProductInterface $product): array
    {
        $allowedAttributes = $this->configurableProductHelper->getAllowAttributes($product);
        $attributes = [];
        foreach ($allowedAttributes as $attribute) {
            $attributes[$attribute->getAttributeId()] = $attribute;
        }

        return $attributes;
    }
}
