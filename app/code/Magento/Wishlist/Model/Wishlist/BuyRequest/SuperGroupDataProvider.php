<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Model\Wishlist\BuyRequest;

use Magento\Wishlist\Model\Wishlist\Data\WishlistItem;

/**
 * Data provider for grouped product buy requests
 */
class SuperGroupDataProvider implements BuyRequestDataProviderInterface
{
    private const PROVIDER_OPTION_TYPE = 'grouped';

    /**
     * @inheritdoc
     *
     * @phpcs:disable Magento2.Functions.DiscouragedFunction
     */
    public function execute(WishlistItem $wishlistItemData, ?int $productId): array
    {
        $groupedData = [];

        foreach ($wishlistItemData->getSelectedOptions() as $optionData) {
            $optionData = \explode('/', base64_decode($optionData->getId()));

            if ($this->isProviderApplicable($optionData) === false) {
                continue;
            }

            [, $simpleProductId, $quantity] = $optionData;

            $groupedData[$simpleProductId] = $quantity;
        }

        if (empty($groupedData)) {
            return $groupedData;
        }

        $result = ['super_group' => $groupedData];

        if ($productId) {
            $result += ['product' => $productId];
        }

        return $result;
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
