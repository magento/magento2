<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Model\Export;

class SearchResultIterator implements \Iterator
{
    /**
     * @var array
     */
    protected $items;

    /**
     * @param array $items
     */
    public function __construct(
        array $items
    ) {
        $this->items = $items;
    }

    /**
     * @return array|mixed
     */
    public function current()
    {
        return current($this->items);
    }

    /**
     * @return int|mixed
     */
    public function key()
    {
        return key($this->items);
    }

    /**
     * @return void
     */
    public function next()
    {
        next($this->items);
    }

    /**
     * @return void
     */
    public function rewind()
    {
        reset($this->items);
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->key() !== null;
    }
}
