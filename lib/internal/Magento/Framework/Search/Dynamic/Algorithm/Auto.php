<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Dynamic\Algorithm;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Search\Adapter\OptionsInterface;
use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Request\BucketInterface;

class Auto implements AlgorithmInterface
{
    /**
     * @var DataProviderInterface
     */
    private $dataProvider;

    /**
     * @var OptionsInterface
     */
    private $options;

    /**
     * @param DataProviderInterface $dataProvider
     * @param OptionsInterface $options
     */
    public function __construct(DataProviderInterface $dataProvider, OptionsInterface $options)
    {
        $this->dataProvider = $dataProvider;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(
        BucketInterface $bucket,
        array $dimensions,
        Table $entityIdsTable
    ) {
        $data = [];
        $range = $this->dataProvider->getRange();
        if (!$range && !empty($entityIdsTable)) {
            $range = $this->getRange($bucket, $dimensions, $entityIdsTable);
            $dbRanges = $this->dataProvider->getAggregation($bucket, $dimensions, $range, $entityIdsTable, 'count');
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
    private function getRange($bucket, array $dimensions, Table $entityIdsTable)
    {
        $maxPrice = $this->getMaxPriceInt($entityIdsTable);
        $index = 1;
        do {
            $range = pow(10, strlen(floor($maxPrice)) - $index);
            $items = $this->dataProvider->getAggregation($bucket, $dimensions, $range, $entityIdsTable);
            $index++;
        } while ($range > $this->getMinRangePower() && count($items) < 2);

        return $range;
    }

    /**
     * Get maximum price from layer products set
     *
     * @param int[] $entityIdsTable
     * @return float
     */
    private function getMaxPriceInt(Table $entityIdsTable)
    {
        $aggregations = $this->dataProvider->getAggregations($entityIdsTable);
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
        $options = $this->options->get();

        return $options['min_range_power'];
    }
}
