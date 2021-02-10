<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Model\Wishlist\BuyRequest;

use Magento\Wishlist\Model\Wishlist\Data\WishlistItem;

/**
 * Data provider for bundle product buy requests
 */
class BundleDataProvider implements BuyRequestDataProviderInterface
{
    private const PROVIDER_OPTION_TYPE = 'bundle';

    /**
     * @inheritdoc
     *
     * @phpcs:disable Magento2.Functions.DiscouragedFunction
     */
    public function execute(WishlistItem $wishlistItem, ?int $productId): array
    {
        $bundleOptionsData = [];

        foreach ($wishlistItem->getSelectedOptions() as $optionData) {
            $optionData = \explode('/', base64_decode($optionData->getId()));

            if ($this->isProviderApplicable($optionData) === false) {
                continue;
            }

            [, $optionId, $optionValueId, $optionQuantity] = $optionData;

            $bundleOptionsData['bundle_option'][$optionId] = $optionValueId;
            $bundleOptionsData['bundle_option_qty'][$optionId] = $optionQuantity;
        }

        return $bundleOptionsData;
    }

    /**
     * Checks whether this provider is applicable for the current option
     *
     * @param array $optionData
     *
     * @return bool
     */
    private function isProviderApplicable(array $optionData): bool
    {
        return $optionData[0] === self::PROVIDER_OPTION_TYPE;
    }
}
