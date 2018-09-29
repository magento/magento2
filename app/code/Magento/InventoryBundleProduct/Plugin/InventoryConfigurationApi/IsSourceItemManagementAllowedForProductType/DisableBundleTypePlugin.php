<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleProduct\Plugin\InventoryConfigurationApi\IsSourceItemManagementAllowedForProductType;

use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;

/**
 * Disable Source items management for Bundle product type.
 */
class DisableBundleTypePlugin
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
        if ($productType === BundleType::TYPE_CODE) {
            return false;
        }

        return $proceed($productType);
    }
}
