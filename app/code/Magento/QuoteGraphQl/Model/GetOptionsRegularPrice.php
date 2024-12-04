<?php
/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model;

use Magento\Catalog\Model\Product\Option;

class GetOptionsRegularPrice
{
    /**
     * Get product options regular price
     *
     * @param array $options
     * @param Option $productOption
     * @return float
     */
    public function execute(array $options, Option $productOption): float
    {
        $price = 0.0;
        foreach ($options as $optionValueId) {
            $optionValue = $productOption->getValueById($optionValueId);
            if ($optionValue) {
                $price += $optionValue->getRegularPrice() ?? 0.0;
            }
        }
        return $price;
    }
}
