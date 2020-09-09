<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Dynamic;

/**
 * Algorithm for layer value filter
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @api
 * @since 100.0.2
 */
class Algorithm
{
    /**
     * Minimal possible value
     */
    const MIN_POSSIBLE_VALUE = .01;

    /**
     * Rounding factor coefficient
     */
    const TEN_POWER_ROUNDING_FACTOR = 4;

    /**
     * Interval deflection coefficient
     */
    const INTERVAL_DEFLECTION_LIMIT = .3;

    /**
     * Standard normal distribution's  a/2 quantile
     * Depends on predefined a. In case of a=0.05
     */
    const STANDARD_NORMAL_DISTRIBUTION = 1.96;

    /**
     * Min and Max number of intervals
     */
    const MIN_INTERVALS_NUMBER = 2;

    const MAX_INTERVALS_NUMBER = 10;

    /**
     * Upper values limit
     *
     * @var null|float
     */
    protected $_upperLimit = null;

    /**
     * Lower values limit
     *
     * @var null|float
     */
    protected $_lowerLimit = null;

    /**
     * Number of segmentation intervals
     *
     * @var null|int
     */
    protected $_intervalsNumber = null;

    /**
     * Upper limits of skipped quantiles
     *
     * @var array
     */
    protected $_skippedQuantilesUpperLimits = [];

    /**
     * Total count of values
     *
     * @var int
     */
    protected $_count = 0;

    /**
     * Current quantile interval
     *
     * @var array [from, to]
     */
    protected $_quantileInterval = [0, 0];

    /**
     * Values of current quantile
     *
     * @var array
     */
    protected $_values = [];

    /**
     * Max value
     *
     * @var float
     */
    protected $_maxValue = 0;

    /**
     * Min value
     *
     * @var float
     */
    protected $_minValue = 0;

    /**
     * Last value query limiter
     *
     * @var array [index, value]
     */
    protected $_lastValueLimiter = [null, 0];

    /**
     * Set lower and upper limit for algorithm
     *
     * @param null|float $lowerLimit
     * @param null|float $upperLimit
     * @return \Magento\Framework\Search\Dynamic\Algorithm
     */
    public function setLimits($lowerLimit = null, $upperLimit = null)
    {
        $this->_lowerLimit = empty($lowerLimit) ? null : (double)$lowerLimit;
        $this->_upperLimit = empty($upperLimit) ? null : (double)$upperLimit;

        return $this;
    }

    /**
     * Set statistics
     *
     * @param float $min
     * @param float $max
     * @param float $standardDeviation
     * @param int $count
     * @return $this
     */
    public function setStatistics($min, $max, $standardDeviation, $count)
    {
        $this->_count = $count;
        $this->_minValue = $min;
        $this->_maxValue = $max;
        $valueRange = $max - $min;
        if ($count < 2 || $valueRange <= 0) {
            //Same value couldn't be separated with several intervals
            $this->_intervalsNumber = 1;

            return $this;
        }

        if ($standardDeviation <= 0) {
            $intervalsNumber = pow(10, self::TEN_POWER_ROUNDING_FACTOR);
        } else {
            $intervalsNumber = $valueRange * pow($count, 1 / 3) / (3.5 * $standardDeviation);
        }
        $this->_intervalsNumber = max(ceil($intervalsNumber), self::MIN_INTERVALS_NUMBER);
        $this->_intervalsNumber = (int)min($this->_intervalsNumber, self::MAX_INTERVALS_NUMBER);

        return $this;
    }

    /**
     * Calculate separators, each contains 'from', 'to' and 'count'
     *
     * @param IntervalInterface $interval
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function calculateSeparators(IntervalInterface $interval)
    {
        $result = [];
        $lastCount = 0;
        $intervalFirstValue = $this->_minValue;
        $lastSeparator = $this->_lowerLimit === null ? 0 : $this->_lowerLimit;
        // phpcs:ignore Generic.CodeAnalysis.ForLoopWithTestFunctionCall
        for ($intervalNumber = 1; $intervalNumber < $this->getIntervalsNumber(); ++$intervalNumber) {
            $separator = $this->_findValueSeparator($intervalNumber, $interval);
            if (empty($separator)) {
                continue;
            }
            if ($this->_quantileInterval[0] == 0) {
                $intervalFirstValue = $this->_values[0];
            }
            $separatorCandidate = false;
            $newIntervalFirstValue = $intervalFirstValue;
            $newLastSeparator = $lastSeparator;

            $valuesPerInterval = $this->_count / $this->_getCalculatedIntervalsNumber();
            while (!empty($separator) && !array_key_exists($intervalNumber, $result)) {
                $separatorsPortion = array_shift($separator);
                $bestSeparator = $this->_findBestSeparator($intervalNumber, $separatorsPortion);
                if ($bestSeparator && $bestSeparator[2] > 0) {
                    $isEqualValue = $intervalFirstValue ==
                    $this->_values[$bestSeparator[2] - 1] ? $this->_values[0] : false;
                    $count = $bestSeparator[2] + $this->_quantileInterval[0] - $lastCount;
                    $separatorData = [
                        'from' => $isEqualValue !== false ? $isEqualValue : $lastSeparator,
                        'to' => $isEqualValue !== false ? $isEqualValue : $bestSeparator[1],
                        'count' => $count,
                    ];
                    if (abs(1 - $count / $valuesPerInterval) <= self::INTERVAL_DEFLECTION_LIMIT) {
                        $newLastSeparator = $bestSeparator[1];
                        $newIntervalFirstValue = $this->_values[$bestSeparator[2]];
                        $result[$intervalNumber] = $separatorData;
                    } elseif (!$separatorCandidate || $bestSeparator[0] < $separatorCandidate[0]) {
                        $separatorCandidate = [
                            $bestSeparator[0],
                            $separatorData,
                            $bestSeparator[1],
                            $this->_values[$bestSeparator[2]],
                        ];
                    }
                }
            }

            if (!array_key_exists($intervalNumber, $result) && $separatorCandidate) {
                $newLastSeparator = $separatorCandidate[2];
                $newIntervalFirstValue = $separatorCandidate[3];
                $result[$intervalNumber] = $separatorCandidate[1];
            }

            if (array_key_exists($intervalNumber, $result)) {
                $lastSeparator = $newLastSeparator;
                $intervalFirstValue = $newIntervalFirstValue;
                $valueIndex = $this->_binarySearch($lastSeparator);
                $lastCount += $result[$intervalNumber]['count'];
                if ($valueIndex != -1 && $lastSeparator > $this->_lastValueLimiter[1]) {
                    $this->_lastValueLimiter = [$valueIndex + $this->_quantileInterval[0], $lastSeparator];
                }
            }
        }
        if ($this->_lastValueLimiter[0] < $this->_count) {
            $isEqualValue = $intervalFirstValue == $this->_maxValue ? $intervalFirstValue : false;
            $result[$this->getIntervalsNumber()] = [
                'from' => $isEqualValue ? $isEqualValue : $lastSeparator,
                'to' => $isEqualValue ? $isEqualValue : ($this->_upperLimit === null ? '' : $this->_upperLimit),
                'count' => $this->_count - $lastCount,
            ];
        }

        return array_values($result);
    }

    /**
     * Get amount of segmentation intervals
     *
     * @return int
     */
    protected function getIntervalsNumber()
    {
        if ($this->_intervalsNumber !== null) {
            return $this->_intervalsNumber;
        }

        return 1;
    }

    /**
     * Find value separator for the quantile
     *
     * @param int $quantileNumber should be from 1 to n-1 where n is number of intervals
     * @param IntervalInterface $interval
     * @return array|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _findValueSeparator($quantileNumber, IntervalInterface $interval)
    {
        if ($quantileNumber < 1 || $quantileNumber >= $this->getIntervalsNumber()) {
            return null;
        }

        $values = [];
        $quantileInterval = $this->_getQuantileInterval($quantileNumber);
        $intervalValuesCount = $quantileInterval[1] - $quantileInterval[0] + 1;
        $offset = $quantileInterval[0];
        if ($this->_lastValueLimiter[0] !== null) {
            $offset -= $this->_lastValueLimiter[0];
        }
        if ($offset < 0) {
            $intervalValuesCount += $offset;
            $values = array_slice(
                $this->_values,
                $this->_lastValueLimiter[0] + $offset - $this->_quantileInterval[0],
                -$offset
            );
            $offset = 0;
        }
        $lowerValue = $this->_lastValueLimiter[1];
        if ($this->_lowerLimit !== null) {
            $lowerValue = max($lowerValue, $this->_lowerLimit);
        }
        if ($intervalValuesCount >= 0) {
            $values = array_merge(
                $values,
                $interval->load($intervalValuesCount + 1, $offset, $lowerValue, $this->_upperLimit)
            );
        }
        // phpcs:ignore Generic.CodeAnalysis.ForLoopWithTestFunctionCall
        $lastValue = $this->offsetLimits($intervalValuesCount, $values);
        $bestRoundValue = [];

        if (count($values) > 0) {
            if ($lastValue == $values[0]) {
                if ($quantileNumber == 1 && $offset) {
                    $additionalValues = $interval->loadPrevious($lastValue, $quantileInterval[0], $this->_lowerLimit);
                    if ($additionalValues) {
                        $quantileInterval[0] -= count($additionalValues);
                        $values = array_merge($additionalValues, $values);
                        $bestRoundValue = $this->_findRoundValue(
                            $values[0] + self::MIN_POSSIBLE_VALUE / 10,
                            $lastValue,
                            false
                        );
                    }
                }
                if ($quantileNumber == $this->getIntervalsNumber() - 1) {
                    $valuesCount = count($values);
                    if ($values[$valuesCount - 1] > $lastValue) {
                        $additionalValues = [$values[$valuesCount - 1]];
                    } else {
                        $additionalValues = $interval->loadNext(
                            $lastValue,
                            $this->_count - $quantileInterval[0] - count($values),
                            $this->_upperLimit
                        );
                    }
                    if ($additionalValues) {
                        $quantileInterval[1] = $quantileInterval[0] + count($values) - 1;
                        if ($values[$valuesCount - 1] <= $lastValue) {
                            $quantileInterval[1] += count($additionalValues);
                            $values = array_merge($values, $additionalValues);
                        }
                        $upperBestRoundValue = $this->_findRoundValue(
                            $lastValue + self::MIN_POSSIBLE_VALUE / 10,
                            $values[count($values) - 1],
                            false
                        );
                        $this->_mergeRoundValues($bestRoundValue, $upperBestRoundValue);
                    }
                }
            } else {
                $bestRoundValue = $this->_findRoundValue(
                    $values[0] + self::MIN_POSSIBLE_VALUE / 10,
                    $lastValue
                );
            }
        }

        $this->_quantileInterval = $quantileInterval;
        $this->_values = $values;

        if (empty($bestRoundValue)) {
            $this->_skippedQuantilesUpperLimits[$quantileNumber] = $quantileInterval[1];

            return $bestRoundValue;
        }

        $valuesCount = count($values);
        if ($values[$valuesCount - 1] > $lastValue) {
            $this->_lastValueLimiter = [$quantileInterval[0] + $valuesCount - 1, $values[$valuesCount - 1]];
        }

        ksort($bestRoundValue, SORT_NUMERIC);
        foreach ($bestRoundValue as $index => &$bestRoundValueValues) {
            if (empty($bestRoundValueValues)) {
                unset($bestRoundValue[$index]);
            } else {
                sort($bestRoundValueValues);
            }
        }

        return array_reverse($bestRoundValue);
    }

    /**
     * Get quantile interval
     *
     * @param int $quantileNumber should be from 1 to n-1 where n is number of intervals
     * @return null|float[] [floatMin,floatMax]
     */
    protected function _getQuantileInterval($quantileNumber)
    {
        if ($quantileNumber < 1 || $quantileNumber >= $this->getIntervalsNumber()) {
            return null;
        }
        $quantile = $this->_getQuantile($quantileNumber);
        $deflectionLimit = floor($this->_count / 2 / $this->getIntervalsNumber());
        $limits = [
            min(floor($quantile - $deflectionLimit), floor($quantile)),
            max(ceil($quantile + $deflectionLimit - 1), ceil($quantile)),
        ];

        $sqrtParam = $this->_count * $quantileNumber * ($this->getIntervalsNumber() - $quantileNumber);
        $deflection = self::STANDARD_NORMAL_DISTRIBUTION * sqrt($sqrtParam) / $this->getIntervalsNumber();
        $left = max(floor($quantile - $deflection - 1), $limits[0], 0);
        if (array_key_exists($quantileNumber - 1, $this->_skippedQuantilesUpperLimits)
            && $left > $this->_skippedQuantilesUpperLimits[$quantileNumber - 1]
        ) {
            $left = $this->_skippedQuantilesUpperLimits[$quantileNumber - 1];
        }
        $right = min(ceil($quantile + $deflection), $limits[1], $this->_count - 1);

        return [$left, $right];
    }

    /**
     * Get quantile
     *
     * @param int $quantileNumber should be from 1 to n-1 where n is number of intervals
     * @return float|null
     */
    protected function _getQuantile($quantileNumber)
    {
        if ($quantileNumber < 1 || $quantileNumber >= $this->getIntervalsNumber()) {
            return 0;
        }

        return $quantileNumber * $this->_count / $this->getIntervalsNumber() - .5;
    }

    /**
     * Find max rounding factor with given value range
     *
     * @param float $lowerValue
     * @param float $upperValue
     * @param bool $returnEmpty whether empty result is acceptable
     * @param null|float $roundingFactor if given, checks for range to contain the factor
     * @return false|array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _findRoundValue($lowerValue, $upperValue, $returnEmpty = true, $roundingFactor = null)
    {
        $lowerValue = round($lowerValue, 3);
        $upperValue = round($upperValue, 3);

        if ($roundingFactor !== null) {
            // Can't separate if values are equal
            if ($lowerValue >= $upperValue) {
                if ($lowerValue > $upperValue || $returnEmpty) {
                    return false;
                }
            }
            // round is used for such examples: (1194.32 / 0.02) or (5 / 100000)
            $lowerDivision = ceil(round($lowerValue / $roundingFactor, self::TEN_POWER_ROUNDING_FACTOR + 3));
            $upperDivision = floor(round($upperValue / $roundingFactor, self::TEN_POWER_ROUNDING_FACTOR + 3));

            $result = [];
            if ($upperDivision <= 0 || $upperDivision - $lowerDivision > 10) {
                return $result;
            }

            for ($i = $lowerDivision; $i <= $upperDivision; ++$i) {
                $result[] = round($i * $roundingFactor, 2);
            }

            return $result;
        }

        $result = [];
        $tenPower = pow(10, self::TEN_POWER_ROUNDING_FACTOR);
        $roundingFactorCoefficients = [10, 5, 2];
        while ($tenPower >= self::MIN_POSSIBLE_VALUE) {
            if ($tenPower == self::MIN_POSSIBLE_VALUE) {
                $roundingFactorCoefficients[] = 1;
            }
            foreach ($roundingFactorCoefficients as $roundingFactorCoefficient) {
                $roundingFactorCoefficient *= $tenPower;
                $roundValues = $this->_findRoundValue(
                    $lowerValue,
                    $upperValue,
                    $returnEmpty,
                    $roundingFactorCoefficient
                );
                if ($roundValues) {
                    $index = round(
                        $roundingFactorCoefficient /
                        self::MIN_POSSIBLE_VALUE
                    );
                    $result[$index] = $roundValues;
                }
            }
            $tenPower /= 10;
        }

        return empty($result) ? [1 => []] : $result;
    }

    /**
     * Merge new round values with old ones
     *
     * @param array &$oldRoundValues
     * @param array &$newRoundValues
     * @return void
     */
    protected function _mergeRoundValues(&$oldRoundValues, &$newRoundValues)
    {
        foreach ($newRoundValues as $roundingFactor => $roundValueValues) {
            if (array_key_exists($roundingFactor, $oldRoundValues)) {
                $oldRoundValues[$roundingFactor] = array_unique(
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                    array_merge($oldRoundValues[$roundingFactor], $roundValueValues)
                );
            } else {
                $oldRoundValues[$roundingFactor] = $roundValueValues;
            }
        }
    }

    /**
     * Get intervals number with checking skipped quantiles
     *
     * @return int
     */
    protected function _getCalculatedIntervalsNumber()
    {
        return max(1, $this->getIntervalsNumber() - count($this->_skippedQuantilesUpperLimits));
    }

    /**
     * Get separator nearest to quantile among the separators
     *
     * @param int $quantileNumber
     * @param array $separators
     * @return array|false [deflection, separatorValue, $valueIndex]
     */
    protected function _findBestSeparator($quantileNumber, $separators)
    {
        $result = false;

        $i = 0;
        $valuesCount = count($this->_values);
        while ($i < $valuesCount && !empty($separators)) {
            $i = $this->_binarySearch($separators[0], [$i]);
            if ($i == -1) {
                break;
            }

            $separator = array_shift($separators);

            $deflection = abs(
                $quantileNumber * $this->_count -
                ($this->_quantileInterval[0] +
                    $i) * $this->_getCalculatedIntervalsNumber()
            );
            if (!$result || $deflection < $result[0]) {
                $result = [$deflection, $separator, $i];
            }
        }

        return $result ? $result : false;
    }

    /**
     * Search first index of value, that satisfy conditions to be 'greater or equal' than $value
     *
     * Returns -1 if index was not found
     *
     * @param float $value
     * @param null|float[] $limits search [from, to]
     * @return int
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _binarySearch($value, $limits = null)
    {
        if (empty($this->_values)) {
            return -1;
        }

        if (!is_array($limits)) {
            $limits = [];
        }
        if (!isset($limits[0])) {
            $limits[0] = 0;
        }
        if (!isset($limits[1])) {
            $limits[1] = count($this->_values) - 1;
        }

        if ($limits[0] > $limits[1] || $this->_values[$limits[1]] < $value) {
            return -1;
        }

        if ($limits[1] - $limits[0] <= 1) {
            return $this->_values[$limits[0]] < $value ? $limits[1] : $limits[0];
        }

        $separator = floor(($limits[0] + $limits[1]) / 2);
        if ($this->_values[$separator] < $value) {
            $limits[0] = $separator + 1;
        } else {
            $limits[1] = $separator;
        }

        return $this->_binarySearch($value, [$limits[0], $limits[1]]);
    }

    /**
     * Get the offsetLimit value
     *
     * @param float $intervalValuesCount
     * @param array $values
     */
    private function offsetLimits(float $intervalValuesCount, array $values)
    {
        if (array_key_exists((int)$intervalValuesCount - 1, $values)) {
            return $values[$intervalValuesCount - 1];
        }
    }
}
