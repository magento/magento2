<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Model\Export;

/**
 * Class \Magento\Ui\Model\Export\SearchResultIterator
 *
 * @since 2.0.0
 */
class SearchResultIterator implements \Iterator
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $items;

    /**
     * @param array $items
     * @since 2.0.0
     */
    public function __construct(
        array $items
    ) {
        $this->items = $items;
    }

    /**
     * @return array|mixed
     * @since 2.0.0
     */
    public function current()
    {
        return current($this->items);
    }

    /**
     * @return int|mixed
     * @since 2.0.0
     */
    public function key()
    {
        return key($this->items);
    }

    /**
     * @return void
     * @since 2.0.0
     */
    public function next()
    {
        next($this->items);
    }

    /**
     * @return void
     * @since 2.0.0
     */
    public function rewind()
    {
        reset($this->items);
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function valid()
    {
        return $this->key() !== null;
    }
}
