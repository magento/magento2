<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Options;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProductGraphQl\Model\Options\DataProvider\Variant;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Retrieve metadata for configurable option selection.
 */
class Metadata
{
    /**
     * @var Data
     */
    private $configurableProductHelper;

    /**
     * @var SelectionUidFormatter
     */
    private $selectionUidFormatter;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Variant
     */
    private $variant;

    /**
     * @param Data $configurableProductHelper
     * @param SelectionUidFormatter $selectionUidFormatter
     * @param ProductRepositoryInterface $productRepository
     * @param Variant $variant
     */
    public function __construct(
        Data $configurableProductHelper,
        SelectionUidFormatter $selectionUidFormatter,
        ProductRepositoryInterface $productRepository,
        Variant $variant
    ) {
        $this->configurableProductHelper = $configurableProductHelper;
        $this->selectionUidFormatter = $selectionUidFormatter;
        $this->productRepository = $productRepository;
        $this->variant = $variant;
    }

    /**
     * Load available selections from configurable options.
     *
     * @param ProductInterface $product
     * @param array $selectedOptionsUid
     * @return array
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getAvailableSelections(
        ProductInterface $product,
        array $selectedOptionsUid
    ): array {
        $options = $this->configurableProductHelper->getOptions($product, $this->getAllowProducts($product));
        $selectedOptions = $this->selectionUidFormatter->extract($selectedOptionsUid);
        $attributeCodes = $this->getAttributeCodes($product);
        $availableSelections = $availableProducts = $variantData = [];

        if (isset($options['index']) && $options['index']) {
            foreach ($options['index'] as $productId => $productOptions) {
                if (!empty($selectedOptions) && !$this->hasProductRequiredOptions($selectedOptions, $productOptions)) {
                    continue;
                }

                $availableProducts[] = $productId;
                foreach ($productOptions as $attributeId => $optionIndex) {
                    $uid = $this->selectionUidFormatter->encode($attributeId, (int)$optionIndex);

                    if (isset($availableSelections[$attributeId]['option_value_uids'])
                        && in_array($uid, $availableSelections[$attributeId]['option_value_uids'])
                    ) {
                        continue;
                    }
                    $availableSelections[$attributeId]['option_value_uids'][] = $uid;
                    $availableSelections[$attributeId]['attribute_code'] = $attributeCodes[$attributeId];
                }

                if ($this->hasSelectionProduct($selectedOptions, $productOptions)) {
                    $variantProduct = $this->productRepository->getById($productId);
                    $variantData = $variantProduct->getData();
                    $variantData['model'] = $variantProduct;
                }
            }
        }

        return [
            'options_available_for_selection' => $availableSelections,
            'variant' => $variantData,
            'availableSelectionProducts' => array_unique($availableProducts),
            'product' => $product
        ];
    }

    /**
     * Get allowed products.
     *
     * @param ProductInterface $product
     * @return ProductInterface[]
     */
    public function getAllowProducts(ProductInterface $product): array
    {
        return $this->variant->getSalableVariantsByParent($product) ?? [];
    }

    /**
     * Check if a product has the selected options.
     *
     * @param array $requiredOptions
     * @param array $productOptions
     * @return bool
     */
    private function hasProductRequiredOptions($requiredOptions, $productOptions): bool
    {
        $result = true;
        foreach ($requiredOptions as $attributeId => $optionIndex) {
            if (!isset($productOptions[$attributeId]) || !$productOptions[$attributeId]
                || $optionIndex != $productOptions[$attributeId]
            ) {
                $result = false;
                break;
            }
        }

        return $result;
    }

    /**
     * Check if selected options match a product.
     *
     * @param array $requiredOptions
     * @param array $productOptions
     * @return bool
     */
    private function hasSelectionProduct($requiredOptions, $productOptions): bool
    {
        return $this->hasProductRequiredOptions($productOptions, $requiredOptions);
    }

    /**
     * Retrieve attribute codes
     *
     * @param ProductInterface $product
     * @return string[]
     */
    private function getAttributeCodes(ProductInterface $product): array
    {
        $allowedAttributes = $this->configurableProductHelper->getAllowAttributes($product);
        $attributeCodes = [];
        foreach ($allowedAttributes as $attribute) {
            $attributeCodes[$attribute->getAttributeId()] = $attribute->getProductAttribute()->getAttributeCode();
        }

        return $attributeCodes;
    }
}
