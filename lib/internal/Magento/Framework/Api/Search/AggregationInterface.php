<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
