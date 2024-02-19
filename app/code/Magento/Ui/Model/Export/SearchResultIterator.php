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
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return current($this->items);
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return key($this->items);
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        next($this->items);
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        reset($this->items);
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        return $this->key() !== null;
    }
}
