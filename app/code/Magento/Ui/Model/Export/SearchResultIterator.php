<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Model\Export;

use Iterator;

class SearchResultIterator implements Iterator
{
    /**
     * @param array $items
     */
    public function __construct(
        protected readonly array $items
    ) {
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
