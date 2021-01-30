<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

/**
 * Extracts cart item options from UID
 */
class GetCartItemOptionsFromUID
{
    /**
     * Gets an array of encoded item options with UID, extracts and decodes the values
     *
     * @param array $encodedCustomOptions
     * @return array
     */
    public function execute(array $encodedCustomOptions): array
    {
        $customOptions = [];

        foreach ($encodedCustomOptions['selected_options'] as $selectedOption) {
            [$optionType, $optionId, $optionValueId] = explode('/', base64_decode($selectedOption));
            if ($optionType == 'custom-option') {
                if (isset($customOptions[$optionId])) {
                    $customOptions[$optionId] = [$customOptions[$optionId], $optionValueId];
                } else {
                    $customOptions[$optionId] = $optionValueId;
                }
            }
        }

        foreach ($encodedCustomOptions['entered_options'] as $enteredOption) {
            /* The date normalization is required since the attribute might value is formatted by the system */
            if ($enteredOption['type'] === 'date') {
                $enteredOption['value'] = date('M d, Y', strtotime($enteredOption['value']));
            }
            [$optionType, $optionId] = explode('/', base64_decode($enteredOption['uid']));
            if ($optionType == 'custom-option') {
                $customOptions[$optionId] = $enteredOption['value'];
            }
        }

        return $customOptions;
    }
}
