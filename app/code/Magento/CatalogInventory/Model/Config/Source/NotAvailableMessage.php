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
     * Message config values
     */
    public const VALUE_ONLY_X_OF_Y = 1;
    public const VALUE_NOT_ENOUGH_ITEMS = 2;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [];
        $options[] = [
            'value' => self::VALUE_ONLY_X_OF_Y,
            'label' => __('Only X of Y available'),
        ];
        $options[] = [
            'value' => self::VALUE_NOT_ENOUGH_ITEMS,
            'label' => __('Not enough items for sale'),
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
            self::VALUE_ONLY_X_OF_Y => __('Only X of Y available'),
            self::VALUE_NOT_ENOUGH_ITEMS => __('Not enough items for sale')
        ];
    }
}
