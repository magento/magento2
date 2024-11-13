<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteBundleOptions\Model\Cart\BuyRequest;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Cart\BuyRequest\BuyRequestDataProviderInterface;
use Magento\Quote\Model\Cart\Data\CartItem;

/**
 * Data provider for bundle product buy requests
 */
class BundleDataProvider implements BuyRequestDataProviderInterface
{
    private const OPTION_TYPE = 'bundle';

    /**
     * @inheritdoc
     *
     * @throws LocalizedException
     */
    public function execute(CartItem $cartItem): array
    {
        $bundleOptionsData = [];

        foreach ($cartItem->getSelectedOptions() as $optionData) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $optionData = \explode('/', base64_decode($optionData->getId()));

            if ($this->isProviderApplicable($optionData) === false) {
                continue;
            }
            $this->validateInput($optionData);

            [$optionType, $optionId, $optionValueId, $optionQuantity] = $optionData;
            if ($optionType == self::OPTION_TYPE) {
                $bundleOptionsData['bundle_option'][$optionId] = $optionValueId;
                $bundleOptionsData['bundle_option_qty'][$optionId] = $optionQuantity;
                $bundleOptionsData['bundle_options_data'][$optionId][$optionValueId] = $optionQuantity;
            }
        }
        //for bundle options with custom quantity
        foreach ($cartItem->getEnteredOptions() as $option) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $optionData = \explode('/', base64_decode($option->getUid()));

            if ($this->isProviderApplicable($optionData) === false) {
                continue;
            }
            $this->validateInput($optionData);

            [$optionType, $optionId, $optionValueId] = $optionData;
            if ($optionType == self::OPTION_TYPE) {
                $optionQuantity = $option->getValue();
                $bundleOptionsData['bundle_option'][$optionId] = $optionValueId;
                $bundleOptionsData['bundle_option_qty'][$optionId] = $optionQuantity;
                $bundleOptionsData['bundle_options_data'][$optionId][$optionValueId] = $optionQuantity;
            }
        }

        return $bundleOptionsData;
    }

    /**
     * Checks whether this provider is applicable for the current option
     *
     * @param array $optionData
     * @return bool
     */
    private function isProviderApplicable(array $optionData): bool
    {
        if ($optionData[0] !== self::OPTION_TYPE) {
            return false;
        }

        return true;
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
}
