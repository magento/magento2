<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data;

use Magento\Framework\DB\QueryInterface;

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
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->current;
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        ++$this->key;
        $this->current = $this->searchResult->createDataObject($this->query->fetchItem());
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->key;
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        return !empty($this->current);
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->current = null;
        $this->key = 0;
        $this->query->reset();
        $this->next();
    }
}
