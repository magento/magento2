<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Dynamic\Algorithm;

use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Request\BucketInterface;

class Manual implements AlgorithmInterface
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
        $range = $this->dataProvider->getRange();
        $options = $this->dataProvider->getOptions();
        if (!$range) {
            $range = $options['range_step'];
        }
        $dbRanges = $this->dataProvider->getAggregation($bucket, $dimensions, $range, $entityIds);
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
