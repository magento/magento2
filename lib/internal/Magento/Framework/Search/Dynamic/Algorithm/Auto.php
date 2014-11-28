<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Search\Dynamic\Algorithm;

use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface;
use Magento\Framework\Search\Request\BucketInterface;

class Auto extends AbstractAlgorithm
{
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
     * @param DataProviderInterface $dataProvider
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
