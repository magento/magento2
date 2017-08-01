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
 * @since 2.0.0
 */
class QueryResponse implements ResponseInterface
{
    /**
     * Document Collection
     *
     * @var Document[]
     * @since 2.0.0
     */
    protected $documents;

    /**
     * Aggregation Collection
     *
     * @var AggregationInterface
     * @since 2.0.0
     */
    protected $aggregations;

    /**
     * @param Document[] $documents
     * @param AggregationInterface $aggregations
     * @since 2.0.0
     */
    public function __construct(array $documents, AggregationInterface $aggregations)
    {
        $this->documents = $documents;
        $this->aggregations = $aggregations;
    }

    /**
     * Countable: return count of fields in document
     * @return int
     * @since 2.0.0
     */
    public function count()
    {
        return count($this->documents);
    }

    /**
     * Implementation of \IteratorAggregate::getIterator()
     *
     * @return \ArrayIterator
     * @since 2.0.0
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->documents);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }
}
