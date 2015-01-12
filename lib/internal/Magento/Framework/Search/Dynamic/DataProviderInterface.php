<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Dynamic;

use Magento\Framework\Search\Request\BucketInterface;

interface DataProviderInterface
{
    /**
     * Get range
     *
     * @return int
     */
    public function getRange();

    /**
     * @param int[] $entityIds
     * @return array
     */
    public function getAggregations(array $entityIds);

    /**
     * Get all options
     *
     * @return array
     */
    public function getOptions();

    /**
     * @param BucketInterface $bucket
     * @param array $dimensions
     * @param int[] $entityIds
     * @return \Magento\Framework\Search\Dynamic\IntervalInterface
     */
    public function getInterval(BucketInterface $bucket, array $dimensions, array $entityIds);

    /**
     * @param BucketInterface $bucket
     * @param array $dimensions
     * @param int $range
     * @param int[] $entityIds
     * @return array
     */
    public function getAggregation(BucketInterface $bucket, array $dimensions, $range, array $entityIds);

    /**
     * @param int $range
     * @param array $dbRanges
     * @return array
     */
    public function prepareData($range, array $dbRanges);
}
