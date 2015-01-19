<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Dynamic\Algorithm;

use Magento\Framework\Search\Dynamic\Algorithm;
use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Request\BucketInterface;

class Improved implements AlgorithmInterface
{
    /**
     * @var Algorithm
     */
    private $algorithm;

    /**
     * @var DataProviderInterface
     */
    private $dataProvider;

    /**
     * @param DataProviderInterface $dataProvider
     * @param Algorithm $algorithm
     */
    public function __construct(
        DataProviderInterface $dataProvider,
        Algorithm $algorithm
    ) {
        $this->algorithm = $algorithm;
        $this->dataProvider = $dataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(BucketInterface $bucket, array $dimensions, array $entityIds)
    {
        $aggregations = $this->dataProvider->getAggregations($entityIds);

        $options = $this->dataProvider->getOptions();
        if ($aggregations['count'] < $options['interval_division_limit']) {
            return [];
        }
        $this->algorithm->setStatistics(
            $aggregations['min'],
            $aggregations['max'],
            $aggregations['std'],
            $aggregations['count']
        );

        $this->algorithm->setLimits($aggregations['min'], $aggregations['max'] + 0.01);

        $interval = $this->dataProvider->getInterval($bucket, $dimensions, $entityIds);
        $data = $this->algorithm->calculateSeparators($interval);

        $data[0]['from'] = ''; // We should not calculate min and max value
        $data[count($data) - 1]['to'] = '';

        $dataSize = count($data);
        for ($key = 0; $key < $dataSize; $key++) {
            if (isset($data[$key + 1])) {
                $data[$key]['to'] = $data[$key + 1]['from'];
            }
        }
        return $data;
    }
}
