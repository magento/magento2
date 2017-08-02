<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Search;

/**
 * Faceted data
 * @since 2.0.0
 */
interface AggregationInterface
{
    /**
     * Get Document field
     *
     * @param string $bucketName
     * @return \Magento\Framework\Api\Search\BucketInterface
     * @since 2.0.0
     */
    public function getBucket($bucketName);

    /**
     * Get all Document fields
     *
     * @return \Magento\Framework\Api\Search\BucketInterface[]
     * @since 2.0.0
     */
    public function getBuckets();

    /**
     * Get Document field names
     *
     * @return string[]
     * @since 2.0.0
     */
    public function getBucketNames();
}
