<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Dynamic;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Search\Dynamic\EntityStorage;
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
     * @param EntityStorage $entityStorage
     * @return array
     */
    public function getAggregations(EntityStorage $entityStorage);

    /**
     * @param BucketInterface $bucket
     * @param array $dimensions
     * @param EntityStorage $entityStorage
     * @return IntervalInterface
     */
    public function getInterval(
        BucketInterface $bucket,
        array $dimensions,
        EntityStorage $entityStorage
    );

    /**
     * @param BucketInterface $bucket
     * @param array $dimensions
     * @param int $range
     * @param EntityStorage $entityStorage
     * @return array
     */
    public function getAggregation(
        BucketInterface $bucket,
        array $dimensions,
        $range,
        EntityStorage $entityStorage
    );

    /**
     * @param int $range
     * @param array $dbRanges
     * @return array
     */
    public function prepareData($range, array $dbRanges);
}
