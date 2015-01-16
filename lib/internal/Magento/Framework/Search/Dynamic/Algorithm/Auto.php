<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Dynamic\Algorithm;

use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Request\BucketInterface;

class Auto implements AlgorithmInterface
{
    /**
     * @var DataProviderInterface
     */
    private $dataProvider;

    /**
     * @param DataProviderInterface $dataProvider
     */
    public function __construct(DataProviderInterface $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(BucketInterface $bucket, array $dimensions, array $entityIds)
    {
        $data = [];
        $range = $this->dataProvider->getRange();
        if (!$range) {
            $range = $this->getRange($bucket, $dimensions, $entityIds);
            $dbRanges = $this->dataProvider->getAggregation($bucket, $dimensions, $range, $entityIds, 'count');
            $data = $this->dataProvider->prepareData($range, $dbRanges);
        }

        return $data;
    }

    /**
     * @param BucketInterface $bucket
     * @param array $dimensions
     * @param int[] $entityIds
     * @return number
     */
    private function getRange($bucket, array $dimensions, array $entityIds)
    {
        $maxPrice = $this->getMaxPriceInt($entityIds);
        $index = 1;
        do {
            $range = pow(10, strlen(floor($maxPrice)) - $index);
            $items = $this->dataProvider->getAggregation($bucket, $dimensions, $range, $entityIds, 'count');
            $index++;
        } while ($range > $this->getMinRangePower() && count($items) < 2);

        return $range;
    }

    /**
     * Get maximum price from layer products set
     *
     * @param int[] $entityIds
     * @return float
     */
    private function getMaxPriceInt(array $entityIds)
    {
        $aggregations = $this->dataProvider->getAggregations($entityIds);
        $maxPrice = $aggregations['max'];
        $maxPrice = floor($maxPrice);

        return $maxPrice;
    }

    /**
     * @param DataProviderInterface $dataProvider
     * @return int
     */
    private function getMinRangePower()
    {
        $options = $this->dataProvider->getOptions();

        return $options['min_range_power'];
    }
}
