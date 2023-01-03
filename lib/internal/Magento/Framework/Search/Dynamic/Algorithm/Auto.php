<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Dynamic\Algorithm;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Search\Adapter\OptionsInterface;
use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Dynamic\EntityStorage;
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
     * @inheritdoc
     */
    public function getItems(
        BucketInterface $bucket,
        array $dimensions,
        EntityStorage $entityStorage
    ) {
        $data = [];
        $range = $this->dataProvider->getRange();
        if ($entityStorage->getSource()) {
            $range = !empty($range) ? $range : $this->getRange($bucket, $dimensions, $entityStorage);
            $dbRanges = $this->dataProvider->getAggregation($bucket, $dimensions, $range, $entityStorage);
            $data = $this->dataProvider->prepareData($range, $dbRanges);
        }

        return $data;
    }

    /**
     * Returns price range.
     *
     * @param BucketInterface $bucket
     * @param array $dimensions
     * @param EntityStorage $entityStorage
     * @return number
     */
    private function getRange($bucket, array $dimensions, EntityStorage $entityStorage)
    {
        $maxPrice = $this->getMaxPriceInt($entityStorage);
        $index = 1;
        do {
            $range = pow(10, strlen(floor($maxPrice)) - $index);
            $items = $this->dataProvider->getAggregation($bucket, $dimensions, $range, $entityStorage);
            $index++;
        } while ($range > $this->getMinRangePower() && count($items) < 2);

        return $range;
    }

    /**
     * Get maximum price from layer products set
     *
     * @param EntityStorage $entityStorage
     * @return float
     */
    private function getMaxPriceInt(EntityStorage $entityStorage)
    {
        $aggregations = $this->dataProvider->getAggregations($entityStorage);
        return ($aggregations['max'] !== null) ? floor($aggregations['max']) : 0;
    }

    /**
     * Return Minimal range power.
     *
     * @return int
     */
    private function getMinRangePower()
    {
        $options = $this->options->get();

        return $options['min_range_power'];
    }
}
