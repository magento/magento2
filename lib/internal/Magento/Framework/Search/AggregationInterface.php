<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search;

/**
 * Faceted data
 */
interface AggregationInterface extends \IteratorAggregate
{
    /**
     * Get Document field
     *
     * @param string $bucketName
     * @return BucketInterface
     */
    public function getBucket($bucketName);

    /**
     * Get Document field names
     *
     * @return string[]
     */
    public function getBucketNames();
}
