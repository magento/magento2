<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Argument\Interpreter;

use Magento\Framework\Data\Argument\InterpreterInterface;

/**
 * Interpreter of array data type that supports arrays of unlimited depth
 */
class ArrayType implements InterpreterInterface
{
    /**
     * Interpreter of individual array item
     *
     * @var InterpreterInterface
     */
    private $itemInterpreter;

    /**
     * @param InterpreterInterface $itemInterpreter
     */
    public function __construct(InterpreterInterface $itemInterpreter)
    {
        $this->itemInterpreter = $itemInterpreter;
    }

    /**
     * {@inheritdoc}
     * @return array
     * @throws \InvalidArgumentException
     */
    public function evaluate(array $data)
    {
        $items = isset($data['item']) ? $data['item'] : [];
        if (!is_array($items)) {
            throw new \InvalidArgumentException('Array items are expected.');
        }
        $result = [];
        $items = $this->sortItems($items);
        foreach ($items as $itemKey => $itemData) {
            $result[$itemKey] = $this->itemInterpreter->evaluate($itemData);
        }
        return $result;
    }

    /**
     * Sort items by sort order attribute.
     *
     * @param array $items
     * @return array
     */
    private function sortItems($items)
    {
        $sortOrderDefined = $this->isSortOrderDefined($items);
        if ($sortOrderDefined) {
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
    private function compareItems($firstItemKey, $secondItemKey, $indexedItems)
    {
        $firstItem = $indexedItems[$firstItemKey]['item'];
        $secondItem = $indexedItems[$secondItemKey]['item'];
        $firstValue = 0;
        $secondValue = 0;
        if (isset($firstItem['sortOrder'])) {
            $firstValue = intval($firstItem['sortOrder']);
        }

        if (isset($secondItem['sortOrder'])) {
            $secondValue = intval($secondItem['sortOrder']);
        }

        if ($firstValue == $secondValue) {
            // These keys reflect initial relative position of items.
            // Allows stable sort for items with equal 'sortOrder'
            return $firstItemKey < $secondItemKey ? -1 : 1;
        }
        return $firstValue < $secondValue ? -1 : 1;
    }

    /**
     * Determine if a sort order exists for any of the items.
     *
     * @param array $items
     * @return bool
     */
    private function isSortOrderDefined($items)
    {
        foreach ($items as $itemData) {
            if (isset($itemData['sortOrder'])) {
                return true;
            }
        }
        return false;
    }
}
