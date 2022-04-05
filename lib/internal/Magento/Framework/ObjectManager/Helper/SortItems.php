<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Helper;

/**
 * Helper for classes which implement Sort Logic for array Items.
 */
class SortItems
{
    /**
     * Sort items by sort order attribute.
     *
     * @param array $items
     * @return array
     */
    public function sortItems(array $items): array
    {
        $sortOrderDefined = $this->isSortOrderDefined($items);
        if ($sortOrderDefined) {
            if (!$this->isMultiSortOrder($items)) {
                $indexedItems = [];
                foreach ($items as $key => $item) {
                    $indexedItems[] = ['key' => $key, 'item' => $item];
                }
                uksort(
                    $indexedItems,
                    function ($firstItemKey, $secondItemKey) use ($indexedItems) {
                        return $this->compareItems($firstItemKey, $secondItemKey, $indexedItems);
                    }
                );
                // Convert array of sorted items back to initial format
                $items = [];
                foreach ($indexedItems as $indexedItem) {
                    $items[$indexedItem['key']] = $indexedItem['item'];
                }
            } else {
                $indexedItem = [];
                foreach ($items as $key => $itemData) {
                    foreach ($itemData as $itemKey => $item) {
                        $indexedItem[] = ['parent'=>$key, 'key' => $itemKey, 'item' => $item];
                    }
                }

                uksort(
                    $indexedItem,
                    function ($firstItemKey, $secondItemKey) use ($indexedItem) {
                        return $this->compareItems($firstItemKey, $secondItemKey, $indexedItem);
                    }
                );
                $items = [];
                foreach ($indexedItem as $iItem) {
                    $items[$iItem['parent']][$iItem['key']] = $iItem['item'];
                }
            }
        }

        return $items;
    }

    /**
     * Compare sortOrder of item
     *
     * @param mixed $firstItemKey
     * @param mixed $secondItemKey
     * @param array $indexedItems
     * @return int
     */
    private function compareItems($firstItemKey, $secondItemKey, array $indexedItems): int
    {
        $firstItem = $indexedItems[$firstItemKey]['item'];
        $secondItem = $indexedItems[$secondItemKey]['item'];
        $firstValue = 0;
        $secondValue = 0;
        if (isset($firstItem['sortOrder'])) {
            $firstValue = (int)$firstItem['sortOrder'];
        }

        if (isset($secondItem['sortOrder'])) {
            $secondValue = (int)$secondItem['sortOrder'];
        }

        if ($firstValue == $secondValue) {
            // These keys reflect initial relative position of items.
            // Allows stable sort for items with equal 'sortOrder'
            return $firstValue <=> $secondValue;
        }
        return $firstValue <=> $secondValue;
    }

    /**
     * Determine if a sort order exists for any of the items.
     *
     * @param array $items
     * @return bool
     */
    private function isSortOrderDefined(array $items): bool
    {
        $isSortOrder = false;

        array_walk($items, function ($value) use (&$isSortOrder) {
            if (!!is_array($value)) {
                if (isset($value['sortOrder'])) {
                    $isSortOrder = true;
                } else {
                    array_walk($value, function ($valueData) use (&$isSortOrder) {
                        if (isset($valueData['sortOrder'])) {
                            $isSortOrder = true;
                        }
                    });
                }
            }
        });

        return $isSortOrder;
    }
    /**
     * Determine if a sort order exists for any of the items.
     *
     * @param array $items
     * @return bool
     */
    private function isMultiSortOrder(array $items): bool
    {
        $isMultiSortOrder = false;

        array_walk($items, function ($value) use (&$isMultiSortOrder) {
            if (!!is_array($value)) {
                array_walk($value, function ($valueData) use (&$isMultiSortOrder) {
                    $isMultiSortOrder = isset($valueData['sortOrder']);
                });
            }
        });

        return $isMultiSortOrder;
    }
}
