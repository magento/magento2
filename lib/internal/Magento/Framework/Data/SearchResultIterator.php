<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data;

use Magento\Framework\DB\QueryInterface;

/**
 * Class SearchResultIterator
 */
class SearchResultIterator implements \Iterator
{
    /**
     * @var SearchResultInterface
     */
    protected $searchResult;

    /**
     * @var QueryInterface
     */
    protected $query;

    /**
     * @var array
     */
    protected $current;

    /**
     * @var int
     */
    protected $key = 0;

    /**
     * @param AbstractSearchResult $searchResult
     * @param QueryInterface $query
     */
    public function __construct(AbstractSearchResult $searchResult, QueryInterface $query)
    {
        $this->searchResult = $searchResult;
        $this->query = $query;
    }

    /**
     * @return array|mixed
     */
    public function current()
    {
        return $this->current;
    }

    /**
     * @return void
     */
    public function next()
    {
        ++$this->key;
        $this->current = $this->searchResult->createDataObject($this->query->fetchItem());
    }

    /**
     * @return int|mixed
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return !empty($this->current);
    }

    /**
     * @return void
     */
    public function rewind()
    {
        $this->current = null;
        $this->key = 0;
        $this->query->reset();
        $this->next();
    }
}
