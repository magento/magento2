<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Dynamic\Algorithm;

use Magento\Framework\Search\Adapter\OptionsInterface;
use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Dynamic\EntityStorage;
use Magento\Framework\Search\Request\BucketInterface;

class Manual implements AlgorithmInterface
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
        EntityStorage $entityStorage
    ) {
        $range = $this->dataProvider->getRange();
        $options = $this->options->get();
        if (!$range) {
            $range = $options['range_step'];
        }
        $dbRanges = $this->dataProvider->getAggregation($bucket, $dimensions, $range, $entityStorage);
        $dbRanges = $this->processRange($dbRanges, $options['max_intervals_number']);
        $data = $this->dataProvider->prepareData($range, $dbRanges);

        return $data;
    }

    /**
     * @param array $items \
     * @param int $maxIntervalsNumber
     * @return array
     */
    private function processRange($items, $maxIntervalsNumber)
    {
        $i = 0;
        $lastIndex = null;
        foreach ($items as $k => $v) {
            ++$i;
            if ($i > 1 && $i > $maxIntervalsNumber) {
                $items[$lastIndex] += $v;
                unset($items[$k]);
            } else {
                $lastIndex = $k;
            }
        }

        return $items;
    }
}
