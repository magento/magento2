<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Response;

use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\BucketInterface;

/**
 * Faceted data
 * @api
 * @since 100.0.2
 */
class Aggregation implements AggregationInterface, \IteratorAggregate
{
    /**
     * Buckets array
     *
     * @var BucketInterface[]
     */
    protected $buckets;

    /**
     * @param BucketInterface[] $buckets
     */
    public function __construct(array $buckets)
    {
        $this->buckets = $buckets;
    }

    /**
     * Implementation of \IteratorAggregate::getIterator()
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->buckets);
    }

    /**
     * Get Document field
     *
     * @param string $bucketName
     * @return BucketInterface
     */
    public function getBucket($bucketName)
    {
        return $this->buckets[$bucketName] ?? null;
    }

    /**
     * Get all Document fields
     *
     * @return BucketInterface[]
     */
    public function getBuckets()
    {
        return $this->buckets;
    }

    /**
     * Get Document field names
     *
     * @return string[]
     */
    public function getBucketNames()
    {
        return array_keys($this->buckets);
    }
}
