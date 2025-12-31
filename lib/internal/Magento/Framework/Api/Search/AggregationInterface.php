<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\Search;

/**
 * Interface Aggregation to get faceted data
 *
 * @api
 */
interface AggregationInterface
{
    /**
     * Get Document field
     *
     * @param string $bucketName
     * @return \Magento\Framework\Api\Search\BucketInterface
     */
    public function getBucket($bucketName);

    /**
     * Get all Document fields
     *
     * @return \Magento\Framework\Api\Search\BucketInterface[]
     */
    public function getBuckets();

    /**
     * Get Document field names
     *
     * @return string[]
     */
    public function getBucketNames();
}
