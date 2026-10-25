<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CartMergePreference implements OptionSourceInterface
{
    /**
     * Retrieve options for cart merge preference
     *
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'guest', 'label' => __('Guest Priority – Override with guest cart quantity')],
            ['value' => 'customer', 'label' => __('Customer Priority – Override with customer cart quantity')],
            ['value' => 'merge', 'label' => __('Merge Quantities – Merge quantities of customer and guest cart')]
        ];
    }
}
