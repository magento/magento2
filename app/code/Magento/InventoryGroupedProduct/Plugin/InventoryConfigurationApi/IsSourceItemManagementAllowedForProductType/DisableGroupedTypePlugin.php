<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryGroupedProduct\Plugin\InventoryConfigurationApi\IsSourceItemManagementAllowedForProductType;

use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;

/**
 * Disable Source items management for grouped product type.
 */
class DisableGroupedTypePlugin
{
    /**
     * @param IsSourceItemManagementAllowedForProductTypeInterface $subject
     * @param callable $proceed
     * @param string $productType
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        IsSourceItemManagementAllowedForProductTypeInterface $subject,
        callable $proceed,
        string $productType
    ): bool {
        if ($productType === Grouped::TYPE_CODE) {
            return false;
        }

        return $proceed($productType);
    }
}
