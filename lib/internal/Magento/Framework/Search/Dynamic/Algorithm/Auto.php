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

/**
 * Class \Magento\Framework\Search\Dynamic\Algorithm\Auto
 *
 * @since 2.0.0
 */
class Auto implements AlgorithmInterface
{
    /**
     * @var DataProviderInterface
     * @since 2.0.0
     */
    private $dataProvider;

    /**
     * @var OptionsInterface
     * @since 2.0.0
     */
    private $options;

    /**
     * @param DataProviderInterface $dataProvider
     * @param OptionsInterface $options
     * @since 2.0.0
     */
    public function __construct(DataProviderInterface $dataProvider, OptionsInterface $options)
    {
        $this->dataProvider = $dataProvider;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @param BucketInterface $bucket
     * @param array $dimensions
     * @param EntityStorage $entityStorage
     * @return number
     * @since 2.0.0
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
     * @since 2.0.0
     */
    private function getMaxPriceInt(EntityStorage $entityStorage)
    {
        $aggregations = $this->dataProvider->getAggregations($entityStorage);
        $maxPrice = $aggregations['max'];
        $maxPrice = floor($maxPrice);

        return $maxPrice;
    }

    /**
     * @return int
     * @since 2.0.0
     */
    private function getMinRangePower()
    {
        $options = $this->options->get();

        return $options['min_range_power'];
    }
}
