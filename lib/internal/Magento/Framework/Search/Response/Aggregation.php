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
 * @since 2.0.0
 */
class Aggregation implements AggregationInterface, \IteratorAggregate
{
    /**
     * Buckets array
     *
     * @var BucketInterface[]
     * @since 2.0.0
     */
    protected $buckets;

    /**
     * @param BucketInterface[] $buckets
     * @since 2.0.0
     */
    public function __construct(array $buckets)
    {
        $this->buckets = $buckets;
    }

    /**
     * Implementation of \IteratorAggregate::getIterator()
     *
     * @return \ArrayIterator
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getBucket($bucketName)
    {
        return isset($this->buckets[$bucketName]) ? $this->buckets[$bucketName] : null;
    }

    /**
     * Get all Document fields
     *
     * @return BucketInterface[]
     * @since 2.0.0
     */
    public function getBuckets()
    {
        return $this->buckets;
    }

    /**
     * Get Document field names
     *
     * @return string[]
     * @since 2.0.0
     */
    public function getBucketNames()
    {
        return array_keys($this->buckets);
    }
}
