<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
        uasort(
            $items,
            function ($firstItem, $secondItem) {
                $firstValue = 0;
                $secondValue = 0;
                if (isset($firstItem['sortOrder'])) {
                    $firstValue = intval($firstItem['sortOrder']);
                }

                if (isset($secondItem['sortOrder'])) {
                    $secondValue = intval($secondItem['sortOrder']);
                }

                if ($firstValue == $secondValue) {
                    return 0;
                }
                return $firstValue < $secondValue ? -1 : 1;
            }
        );
        return $items;
    }
}
