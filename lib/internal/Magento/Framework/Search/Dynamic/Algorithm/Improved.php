<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Dynamic\Algorithm;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Search\Adapter\OptionsInterface;
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
     * @var OptionsInterface
     */
    private $options;

    /**
     * @param DataProviderInterface $dataProvider
     * @param Algorithm $algorithm
     * @param OptionsInterface $options
     */
    public function __construct(
        DataProviderInterface $dataProvider,
        Algorithm $algorithm,
        OptionsInterface $options
    ) {
        $this->algorithm = $algorithm;
        $this->dataProvider = $dataProvider;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(
        BucketInterface $bucket,
        array $dimensions,
        \Magento\Framework\Search\Dynamic\EntityStorage $entityStorage
    ) {
        $aggregations = $this->dataProvider->getAggregations($entityStorage);

        $options = $this->options->get();
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

        $interval = $this->dataProvider->getInterval($bucket, $dimensions, $entityStorage);
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
