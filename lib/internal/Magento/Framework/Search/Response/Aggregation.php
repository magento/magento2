<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Response;

use Magento\Framework\Search\AggregationInterface;
use Magento\Framework\Search\BucketInterface;

/**
 * Faceted data
 */
class Aggregation implements AggregationInterface
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
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->buckets);
    }

    /**
     * {@inheritdoc}
     */
    public function getBucket($bucketName)
    {
        return isset($this->buckets[$bucketName]) ? $this->buckets[$bucketName] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getBucketNames()
    {
        return array_keys($this->buckets);
    }
}
