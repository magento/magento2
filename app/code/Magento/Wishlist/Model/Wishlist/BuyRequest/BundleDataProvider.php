<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Model\Wishlist\BuyRequest;

use Magento\Framework\Exception\LocalizedException;
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

            [$optionType, $optionId, $optionValueId, $optionQuantity] = $optionData;

            if ($optionType == self::PROVIDER_OPTION_TYPE) {
                $bundleOptionsData['bundle_option'][$optionId] = $optionValueId;
                $bundleOptionsData['bundle_option_qty'][$optionId] = $optionQuantity;
            }
        }
        //for bundle options with custom quantity
        foreach ($wishlistItem->getEnteredOptions() as $option) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $optionData = \explode('/', base64_decode($option->getUid()));

            if ($this->isProviderApplicable($optionData) === false) {
                continue;
            }
            $this->validateInput($optionData);

            [$optionType, $optionId, $optionValueId] = $optionData;
            if ($optionType == self::PROVIDER_OPTION_TYPE) {
                $optionQuantity = $option->getValue();
                $bundleOptionsData['bundle_option'][$optionId] = $optionValueId;
                $bundleOptionsData['bundle_option_qty'][$optionId] = $optionQuantity;
            }
        }

        return $bundleOptionsData;
    }

    /**
     * Validates the provided options structure
     *
     * @param array $optionData
     * @throws LocalizedException
     */
    private function validateInput(array $optionData): void
    {
        if (count($optionData) !== 4) {
            $errorMessage = __('Wrong format of the entered option data');
            throw new LocalizedException($errorMessage);
        }
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
