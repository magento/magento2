<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Model\Wishlist\BuyRequest;

use Magento\Wishlist\Model\Wishlist\Data\WishlistItem;

/**
 * Data provider for configurable product buy requests
 */
class SuperAttributeDataProvider implements BuyRequestDataProviderInterface
{
    private const PROVIDER_OPTION_TYPE = 'configurable';

    /**
     * @inheritdoc
     *
     * @phpcs:disable Magento2.Functions.DiscouragedFunction
     */
    public function execute(WishlistItem $wishlistItemData, ?int $productId): array
    {
        $configurableData = [];

        foreach ($wishlistItemData->getSelectedOptions() as $optionData) {
            $optionData = \explode('/', base64_decode($optionData->getId()));

            if ($this->isProviderApplicable($optionData) === false) {
                continue;
            }

            [, $attributeId, $valueIndex] = $optionData;

            $configurableData[$attributeId] = $valueIndex;
        }

        if (empty($configurableData)) {
            return $configurableData;
        }

        $result = ['super_attribute' => $configurableData];

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
