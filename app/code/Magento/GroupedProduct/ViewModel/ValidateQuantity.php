<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\ViewModel;

use Magento\CatalogInventory\Model\Product\QuantityValidator;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * ViewModel for Grouped Products Block
 */
class ValidateQuantity implements ArgumentInterface
{
    /**
     * @param Json $serializer
     * @param QuantityValidator $productQuantityValidator
     */
    public function __construct(
        private readonly Json $serializer,
        private readonly QuantityValidator $productQuantityValidator,
    ) {
    }

    /**
     * To get the quantity validators
     *
     * @param int $productId
     * @param int|null $websiteId
     *
     * @return string
     */
    public function getQuantityValidators(int $productId, int|null $websiteId): string
    {
        return $this->serializer->serialize(
            array_merge(
                ['validate-grouped-qty' => '#super-product-table'],
                $this->productQuantityValidator->getData($productId, $websiteId)
            )
        );
    }
}
