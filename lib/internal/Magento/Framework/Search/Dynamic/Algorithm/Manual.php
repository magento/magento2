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

use Magento\Framework\Search\Request\BucketInterface;

class Manual extends AbstractAlgorithm
{
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
