<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

/**
 * Catalog Inventory Config Backend Model
 */
namespace Magento\CatalogInventory\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class NotAvailableMessage implements OptionSourceInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [];
        $options[] = [
            'value' => 1,
            'label' => __('Only X available for sale. Please adjust the quantity to continue'),
        ];
        $options[] = [
            'value' => 2,
            'label' => __('Not enough items for sale. Please adjust the quantity to continue'),
        ];
        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            1 => __('Only X available for sale. Please adjust the quantity to continue'),
            2 => __('Not enough items for sale. Please adjust the quantity to continue')
        ];
    }
}
