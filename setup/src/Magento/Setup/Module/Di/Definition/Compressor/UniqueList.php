<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Definition\Compressor;

class UniqueList
{
    /**
     * List of stored items
     *
     * @var array
     */
    protected $_items = [];

    /**
     * Add item to list and retrieve auto-incremented item position
     *
     * @param mixed $item
     * @return int|bool
     */
    public function getNumber($item)
    {
        if (in_array($item, $this->_items, true)) {
            return array_search($item, $this->_items, true);
        } else {
            $this->_items[] = $item;
            return count($this->_items) - 1;
        }
    }

    /**
     * Represent list as array
     *
     * @return array
     */
    public function asArray()
    {
        return $this->_items;
    }
}
