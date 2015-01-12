<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Response;

use Magento\Framework\Search\Document;
use Magento\Framework\Search\ResponseInterface;

/**
 * Search Response
 */
class QueryResponse implements ResponseInterface, \IteratorAggregate, \Countable
{
    /**
     * Document Collection
     *
     * @var Document[]
     */
    protected $documents;

    /**
     * Aggregation Collection
     *
     * @var Aggregation
     */
    protected $aggregations;

    /**
     * @param Document[] $documents
     * @param Aggregation $aggregations
     */
    public function __construct(array $documents, Aggregation $aggregations)
    {
        $this->documents = $documents;
        $this->aggregations = $aggregations;
    }

    /**
     * Countable: return count of fields in document
     * @return int
     */
    public function count()
    {
        return count($this->documents);
    }

    /**
     * Implementation of \IteratorAggregate::getIterator()
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->documents);
    }

    /**
     * Return Aggregation Collection
     *
     * @return Aggregation
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }
}
