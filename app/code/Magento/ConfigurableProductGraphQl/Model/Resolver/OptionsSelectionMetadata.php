<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Resolver;

use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProductGraphQl\Model\Formatter\Variant as VariantFormatter;
use Magento\ConfigurableProductGraphQl\Model\Options\ConfigurableOptionsMetadata;
use Magento\ConfigurableProductGraphQl\Model\Options\DataProvider\Variant;
use Magento\ConfigurableProductGraphQl\Model\Options\Metadata;
use Magento\ConfigurableProductGraphQl\Model\Options\SelectionUidFormatter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Resolver for options selection
 */
class OptionsSelectionMetadata implements ResolverInterface
{
    /**
     * @var Metadata
     */
    private $configurableSelectionMetadata;

    /**
     * @var ConfigurableOptionsMetadata
     */
    private $configurableOptionsMetadata;

    /**
     * @var SelectionUidFormatter
     */
    private $selectionUidFormatter;

    /**
     * @var Variant
     */
    private $variant;

    /**
     * @var VariantFormatter
     */
    private $variantFormatter;

    /**
     * @var Data
     */
    private $configurableProductHelper;

    /**
     * @param Metadata $configurableSelectionMetadata
     * @param ConfigurableOptionsMetadata $configurableOptionsMetadata
     * @param SelectionUidFormatter $selectionUidFormatter
     * @param Variant $variant
     * @param VariantFormatter $variantFormatter
     * @param Data $configurableProductHelper
     */
    public function __construct(
        Metadata $configurableSelectionMetadata,
        ConfigurableOptionsMetadata $configurableOptionsMetadata,
        SelectionUidFormatter $selectionUidFormatter,
        Variant $variant,
        VariantFormatter $variantFormatter,
        Data $configurableProductHelper
    ) {
        $this->configurableSelectionMetadata = $configurableSelectionMetadata;
        $this->configurableOptionsMetadata = $configurableOptionsMetadata;
        $this->selectionUidFormatter = $selectionUidFormatter;
        $this->variant = $variant;
        $this->variantFormatter = $variantFormatter;
        $this->configurableProductHelper = $configurableProductHelper;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $product = $value['model'];

        $selectionUids = $args['configurableOptionValueUids'] ?? [];
        $selectedOptions = $this->selectionUidFormatter->extract($selectionUids);

        $variants = $this->variant->getSalableVariantsByParent($product);
        $options = $this->configurableProductHelper->getOptions($product, $variants);

        $configurableOptions = $this->configurableOptionsMetadata->getAvailableSelections(
            $product,
            $options,
            $selectedOptions
        );

        $optionsAvailableForSelection = $this->configurableSelectionMetadata->getAvailableSelections(
            $product,
            $args['configurableOptionValueUids'] ?? []
        );

        return [
            'configurable_options' => $configurableOptions,
            'variant' => $this->variantFormatter->format($options, $selectedOptions, $variants),
            'model' => $product,
            'options_available_for_selection' => $optionsAvailableForSelection['options_available_for_selection'],
            'availableSelectionProducts' => $optionsAvailableForSelection['availableSelectionProducts']
        ];
    }
}
