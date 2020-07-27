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
 * @since 100.0.2
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
     * Temporary solution for an existing interface of a fulltext search request in Backward compatibility purposes.
     * Don't use this function.
     * It must be move to different interface.
     * Scope to split Search response interface on two different 'Search' and 'Fulltext Search' contains in MC-16461.
     *
     * @deprecated 102.0.2
     *
     * @return int
     * @since 102.0.2
     */
    public function getTotal(): int
    {
        return $this->total;
    }
}
