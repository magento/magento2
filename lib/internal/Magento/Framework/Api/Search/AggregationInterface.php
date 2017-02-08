<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Search;

/**
 * Faceted data
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
