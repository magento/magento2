<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryBundle\Plugin\InventoryConfiguration\IsSourceItemsAllowedForProductType;

use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\InventoryConfiguration\Model\IsSourceItemsAllowedForProductType;

/**
 * Disable Source items management for Bundle product type.
 */
class DisableBundleTypePlugin
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
        if ($productType === BundleType::TYPE_CODE) {
            return false;
        }

        return $proceed($productType);
    }
}
