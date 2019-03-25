<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Response;

use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\Document;
use Magento\Framework\Search\ResponseInterface;

/**
 * Search Response
 * @api
 */
class QueryResponse implements ResponseInterface
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
     * @var AggregationInterface
     */
    protected $aggregations;

    /**
     * @var int
     */
    private $total;

    /**
     * @param Document[] $documents
     * @param AggregationInterface $aggregations
     * @param int $total
     */
    public function __construct(array $documents, AggregationInterface $aggregations, int $total = 0)
    {
        $this->documents = $documents;
        $this->aggregations = $aggregations;
        $this->total = $total;
    }

    /**
     * Countable: return count of fields in document.
     *
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
     * @inheritdoc
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * @inheritdoc
     */
    public function getTotal(): int
    {
        return $this->total;
    }
}
