<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\CartItem\Precursor;

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
     * Process cart item data to handle parent_sku for configurable products
     *
     * @param array $cartItemData
     * @param ContextInterface $context
     * @return array
     */
    public function process(array $cartItemData, ContextInterface $context): array
    {
        $result = [];

        foreach ($cartItemData as $key => $itemData) {
            $result[$key] = $itemData;

            if (!empty($itemData['parent_sku'])) {
                $parentItemData = [
                    'sku' => $itemData['parent_sku'],
                    'quantity' => $itemData['quantity'],
                    'parent_sku' => null,
                    'selected_options' => $itemData['selected_options'] ?? [],
                    'entered_options' => $itemData['entered_options'] ?? [],
                ];

                $result[] = $parentItemData;
            }
        }

        return $result;
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
