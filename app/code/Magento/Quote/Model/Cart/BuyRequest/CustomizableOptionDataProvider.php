<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Cart\BuyRequest;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Cart\Data\CartItem;

/**
 * Extract buy request elements require for custom options
 */
class CustomizableOptionDataProvider implements BuyRequestDataProviderInterface
{
    private const OPTION_TYPE = 'custom-option';

    /**
     * @inheritdoc
     *
     * @throws LocalizedException
     */
    public function execute(CartItem $cartItem): array
    {
        $customizableOptionsData = [];

        foreach ($cartItem->getSelectedOptions() as $optionData) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $optionData = \explode('/', base64_decode($optionData->getId()));

            if ($this->isProviderApplicable($optionData) === false) {
                continue;
            }
            $this->validateInput($optionData);

            [$optionType, $optionId, $optionValue] = $optionData;
            if ($optionType == self::OPTION_TYPE) {
                $customizableOptionsData[$optionId][] = $optionValue;
            }
        }

        foreach ($cartItem->getEnteredOptions() as $option) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $optionData = \explode('/', base64_decode($option->getUid()));

            if ($this->isProviderApplicable($optionData) === false) {
                continue;
            }

            [$optionType, $optionId] = $optionData;
            if ($optionType == self::OPTION_TYPE) {
                $customizableOptionsData[$optionId][] = $option->getValue();
            }
        }

        return ['options' => $this->flattenOptionValues($customizableOptionsData)];
    }

    /**
     * Flatten option values for non-multiselect customizable options
     *
     * @param array $customizableOptionsData
     * @return array
     */
    private function flattenOptionValues(array $customizableOptionsData): array
    {
        foreach ($customizableOptionsData as $optionId => $optionValue) {
            if (count($optionValue) === 1) {
                $customizableOptionsData[$optionId] = $optionValue[0];
            }
        }

        return $customizableOptionsData;
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
        if (count($optionData) !== 3) {
            throw new LocalizedException(
                __('Wrong format of the entered option data')
            );
        }
    }
}
