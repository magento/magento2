<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\CartItem\Precursor;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\QuoteGraphQl\Model\CartItem\PrecursorInterface;

/**
 * Handles parent-child relationship for configurable products
 */
class ConfigurableProductPrecursor implements PrecursorInterface
{
    /**
     * @var array
     */
    private array $errors = [];

    /**
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {
    }

    /**
     * Process cart item data to handle parent_sku for configurable products
     *
     * @param array $cartItemData
     * @param ContextInterface $context Parameter required by interface but not used
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(array $cartItemData, ContextInterface $context): array
    {
        $processedCartItemData = [];

        foreach ($cartItemData as $cartItemIndex => $cartItem) {
            if (!isset($cartItem['parent_sku'])) {
                $processedCartItemData[$cartItemIndex] = $cartItem;
                continue;
            }

            try {
                $childProduct = $this->productRepository->get($cartItem['sku']);
                $parentProduct = $this->productRepository->get($cartItem['parent_sku']);

                if ($parentProduct->getTypeId() !== Configurable::TYPE_CODE) {
                    $this->errors[] = [
                        'message' => sprintf('Product %s is not a configurable product', $cartItem['parent_sku']),
                        'code' => 'UNDEFINED'
                    ];
                    $processedCartItemData[$cartItemIndex] = $cartItem;
                    continue;
                }

                $configurableOptions = $this->getConfigurableOptions($parentProduct, $childProduct);

                if (empty($configurableOptions)) {
                    $this->errors[] = [
                        'message' => sprintf(
                            'Could not match child product %s with parent %s',
                            $cartItem['sku'],
                            $cartItem['parent_sku']
                        ),
                        'code' => 'UNDEFINED'
                    ];
                    $processedCartItemData[$cartItemIndex] = $cartItem;
                    continue;
                }

                $parentCartItem = [
                    'sku' => $cartItem['parent_sku'],
                    'quantity' => $cartItem['quantity'],
                    'selected_options' => [...$configurableOptions, ...($cartItem['selected_options'] ?? [])],
                    'entered_options' => $cartItem['entered_options'] ?? [],
                    'parent_sku' => null
                ];

                $processedCartItemData[] = $parentCartItem;
                unset($cartItemData[$cartItemIndex]);

            } catch (NoSuchEntityException $e) {
                $this->errors[] = [
                    'message' => $e->getMessage(),
                    'code' => 'UNDEFINED'
                ];
                $processedCartItemData[$cartItemIndex] = $cartItem;
            }
        }

        return $processedCartItemData;
    }

    /**
     * Get configurable option IDs for the simple product
     *
     * @param ProductInterface $parentProduct
     * @param ProductInterface $childProduct
     * @return array
     */
    private function getConfigurableOptions(ProductInterface $parentProduct, ProductInterface $childProduct): array
    {
        $selectedOptions = [];

        /** @var Configurable $configurableType */
        $configurableType = $parentProduct->getTypeInstance();
        $attributes = $configurableType->getConfigurableAttributes($parentProduct);

        $childProductData = $childProduct->getData();

        foreach ($attributes as $attribute) {
            $attributeId = $attribute->getProductAttribute()->getAttributeId();
            $attributeCode = $attribute->getProductAttribute()->getAttributeCode();

            if (!isset($childProductData[$attributeCode])) {
                continue;
            }

            $optionId = $childProductData[$attributeCode];
            $selectedOptions[] = base64_encode("configurable/$attributeId/$optionId");
        }

        return $selectedOptions;
    }

    /**
     * Return collected errors
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
