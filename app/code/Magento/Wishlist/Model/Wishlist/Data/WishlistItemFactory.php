<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Model\Wishlist\Data;

use Magento\Framework\Exception\InputException;

/**
 * Create WishlistItem DTO
 */
class WishlistItemFactory
{
    /**
     * Create wishlist item DTO
     *
     * @param array $data
     *
     * @return WishlistItem
     */
    public function create(array $data): WishlistItem
    {
        return new WishlistItem(
            $data['quantity'],
            $data['sku'] ?? null,
            $data['parent_sku'] ?? null,
            isset($data['wishlist_item_id']) ? (int) $data['wishlist_item_id'] : null,
            $data['description'] ?? null,
            isset($data['selected_options']) ? $this->createSelectedOptions($data['selected_options']) : [],
            isset($data['entered_options']) ? $this->createEnteredOptions($data['entered_options']) : []
        );
    }

    /**
     * Create array of Entered Options
     *
     * @param array $options
     *
     * @return EnteredOption[]
     */
    private function createEnteredOptions(array $options): array
    {
        return \array_map(
            function (array $option) {
                if (!isset($option['uid'], $option['value'])) {
                    throw new InputException(
                        __('Required fields are not present EnteredOption.uid, EnteredOption.value')
                    );
                }
                return new EnteredOption($option['uid'], $option['value']);
            },
            $options
        );
    }

    /**
     * Create array of Selected Options
     *
     * @param string[] $options
     *
     * @return SelectedOption[]
     */
    private function createSelectedOptions(array $options): array
    {
        return \array_map(
            function ($option) {
                return new SelectedOption($option);
            },
            $options
        );
    }
}
