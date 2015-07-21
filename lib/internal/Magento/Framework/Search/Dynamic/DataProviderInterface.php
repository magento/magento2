<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Dynamic;

use Magento\Framework\DB\Ddl\Table;
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
     * @param Table $entityIdsTable
     * @return array
     */
    public function getAggregations(Table $entityIdsTable);

    /**
     * @param BucketInterface $bucket
     * @param array $dimensions
     * @param Table $entityIdsTable
     * @return IntervalInterface
     */
    public function getInterval(
        BucketInterface $bucket,
        array $dimensions,
        Table $entityIdsTable
    );

    /**
     * @param BucketInterface $bucket
     * @param array $dimensions
     * @param int $range
     * @param Table $entityIdsTable
     * @return array
     */
    public function getAggregation(
        BucketInterface $bucket,
        array $dimensions,
        $range,
        Table $entityIdsTable
    );

    /**
     * @param int $range
     * @param array $dbRanges
     * @return array
     */
    public function prepareData($range, array $dbRanges);
}
