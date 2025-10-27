<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Argument\Interpreter;

use InvalidArgumentException;
use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\ObjectManager\Helper\SortItems as SortItemsHelper;

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
    private InterpreterInterface $itemInterpreter;

    /**
     * @var SortItemsHelper
     */
    private SortItemsHelper $sortItemsHelper;

    /**
     * @param InterpreterInterface $itemInterpreter
     * @param SortItemsHelper|null $sortItemsHelper
     */
    public function __construct(InterpreterInterface $itemInterpreter, ?SortItemsHelper $sortItemsHelper = null)
    {
        $this->itemInterpreter = $itemInterpreter;
        $this->sortItemsHelper = $sortItemsHelper ?: new \Magento\Framework\ObjectManager\Helper\SortItems();
    }

    /**
     * @inheritdoc
     * @return array
     * @throws InvalidArgumentException
     */
    public function evaluate(array $data): array
    {
        $items = $data['item'] ?? [];
        if (!is_array($items)) {
            throw new InvalidArgumentException('Array items are expected.');
        }
        $result = [];
        $items = $this->sortItemsHelper->sortItems($items);
        foreach ($items as $itemKey => $itemData) {
            $result[$itemKey] = $this->itemInterpreter->evaluate($itemData);
        }
        return $result;
    }
}
