<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
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
