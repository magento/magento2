<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Model\Import\Source;

use Magento\ImportExport\Model\Import\AbstractSource;

/**
 * JSON import adapter
 */
class Json extends AbstractSource
{
    /**
     * @var array
     */
    private array $items;

    /**
     * @var int
     */
    private int $position = 0;

    /**
     * @var array|int[]|string[] $colNames
     */
    private array $colNames = [];

    /**
     * @param array $items
     */
    public function __construct(array $items)
    {
        // convert all scalar values to strings
        $this->items = array_map(function ($item) {
            return array_map(function ($value) {
                return is_scalar($value) ? (string)$value : $value;
            }, $item);
        }, $items);

        if (isset($this->items[0])) {
            $this->colNames = array_keys($this->items[0]);
        }
        parent::__construct($this->colNames ?? []);
    }

    /**
     * Read next item from JSON data
     *
     * @return array|bool
     */
    protected function _getNextRow()
    {
        if (isset($this->items[$this->position])) {
            return $this->items[$this->position++];
        }
        return false;
    }

    /**
     * Rewind the \Iterator to the first element (\Iterator interface)
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->position = 0;
        parent::rewind();
    }

    /**
     * Seek to a specific position in the data
     *
     * @param int $position
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function seek($position)
    {
        if ($position < 0 || $position >= count($this->items)) {
            throw new \OutOfBoundsException("Invalid seek position ($position)");
        }
        $this->position = $position;
    }
}
