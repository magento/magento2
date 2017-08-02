<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data;

use Magento\Framework\DB\QueryInterface;

/**
 * Class SearchResultIterator
 * @since 2.0.0
 */
class SearchResultIterator implements \Iterator
{
    /**
     * @var SearchResultInterface
     * @since 2.0.0
     */
    protected $searchResult;

    /**
     * @var QueryInterface
     * @since 2.0.0
     */
    protected $query;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $current;

    /**
     * @var int
     * @since 2.0.0
     */
    protected $key = 0;

    /**
     * @param AbstractSearchResult $searchResult
     * @param QueryInterface $query
     * @since 2.0.0
     */
    public function __construct(AbstractSearchResult $searchResult, QueryInterface $query)
    {
        $this->searchResult = $searchResult;
        $this->query = $query;
    }

    /**
     * @return array|mixed
     * @since 2.0.0
     */
    public function current()
    {
        return $this->current;
    }

    /**
     * @return void
     * @since 2.0.0
     */
    public function next()
    {
        ++$this->key;
        $this->current = $this->searchResult->createDataObject($this->query->fetchItem());
    }

    /**
     * @return int|mixed
     * @since 2.0.0
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function valid()
    {
        return !empty($this->current);
    }

    /**
     * @return void
     * @since 2.0.0
     */
    public function rewind()
    {
        $this->current = null;
        $this->key = 0;
        $this->query->reset();
        $this->next();
    }
}
