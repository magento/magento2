<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained from
 * Adobe.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model;

class FormatMoneyTypeData
{
    /**
     * Converts money data to unified format
     *
     * @param array $data
     * @param string $currencyCode
     * @return array
     */
    public function execute(array $data, string $currencyCode): array
    {
        if (isset($data['amount'])) {
            $data['amount'] = [
                'value' => $data['amount'],
                'currency' => $currencyCode
            ];
        }

        /** @deprecated The field should not be used on the storefront */
        $data['base_amount'] = null;

        if (isset($data['price_excl_tax'])) {
            $data['price_excl_tax'] = [
                'value' => $data['price_excl_tax'],
                'currency' => $currencyCode
            ];
        }

        if (isset($data['price_incl_tax'])) {
            $data['price_incl_tax'] = [
                'value' => $data['price_incl_tax'],
                'currency' => $currencyCode
            ];
        }
        return $data;
    }
}
