<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryGroupedProduct\Plugin\InventoryConfiguration\IsSourceItemsAllowedForProductType;

use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\InventoryConfiguration\Model\IsSourceItemsAllowedForProductType;

/**
 * Disable Source items management for grouped product type.
 */
class DisableGroupedTypePlugin
{
    /**
     * @param IsSourceItemsAllowedForProductType $subject
     * @param callable $proceed
     * @param string $productType
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(IsSourceItemsAllowedForProductType $subject, callable $proceed, string $productType)
    {
        if ($productType === Grouped::TYPE_CODE) {
            return false;
        }

        return $proceed($productType);
    }
}