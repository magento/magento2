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
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Algorithm for layer price filter
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Layer_Filter_Price_Algorithm
{
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
     * Upper prices limit
     *
     * @var null|float
     */
    protected $_upperLimit = null;

    /**
     * Lower prices limit
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
    protected $_skippedQuantilesUpperLimits = array();

    /**
     * Total count of prices
     *
     * @var int
     */
    protected $_count = 0;

    /**
     * Prices model
     *
     * @var null|Mage_Catalog_Model_Layer_Filter_Price
     */
    protected $_pricesModel = null;

    /**
     * Current quantile interval
     *
     * @var array [from, to]
     */
    protected $_quantileInterval = array(0, 0);

    /**
     * Prices of current quantile
     *
     * @var array
     */
    protected $_prices = array();

    /**
     * Max price
     *
     * @var float
     */
    protected $_maxPrice = 0;

    /**
     * Min price
     *
     * @var float
     */
    protected $_minPrice = 0;

    /**
     * Last price query limiter
     *
     * @var array [index, value]
     */
    protected $_lastPriceLimiter = array(null, 0);

    /**
     * Set lower and upper limit for algorithm
     *
     * @param null|float $lowerLimit
     * @param null|float $upperLimit
     * @return Mage_Catalog_Model_Layer_Filter_Price_Algorithm
     */
    public function setLimits($lowerLimit = null, $upperLimit = null)
    {
        $this->_lowerLimit = empty($lowerLimit) ? null : (float)$lowerLimit;
        $this->_upperLimit = empty($upperLimit) ? null : (float)$upperLimit;
        return $this;
    }

    /**
     * Search first index of price, that satisfy conditions to be 'greater or equal' than $value
     * Returns -1 if index was not found
     *
     * @param float $value
     * @param null|array $limits search [from, to]
     * @return int
     */
    protected function _binarySearch($value, $limits = null)
    {
        if (empty($this->_prices)) {
            return -1;
        }

        if (!is_array($limits)) {
            $limits = array();
        }
        if (!isset($limits[0])) {
            $limits[0] = 0;
        }
        if (!isset($limits[1])) {
            $limits[1] = count($this->_prices) - 1;
        }

        if ($limits[0] > $limits[1] || $this->_prices[$limits[1]] < $value) {
            return -1;
        }

        if ($limits[1] - $limits[0] <= 1) {
            return ($this->_prices[$limits[0]] < $value) ? $limits[1] : $limits[0];
        }

        $separator = floor(($limits[0] + $limits[1]) / 2);
        if ($this->_prices[$separator] < $value) {
            $limits[0] = $separator + 1;
        } else {
            $limits[1] = $separator;
        }

        return $this->_binarySearch($value, array($limits[0], $limits[1]));
    }

    /**
     * Set prices statistics
     *
     * @param float $min
     * @param float $max
     * @param float $standardDeviation
     * @param int $count
     * @return Mage_Catalog_Model_Layer_Filter_Price_Algorithm
     */
    public function setStatistics($min, $max, $standardDeviation, $count)
    {
        $this->_count = $count;
        $this->_minPrice = $min;
        $this->_maxPrice = $max;
        $priceRange = $max - $min;
        if ($count < 2 || ($priceRange <= 0)) {
            //Same price couldn't be separated with several intervals
            $this->_intervalsNumber = 1;
            return $this;
        }

        if ($standardDeviation <= 0) {
            $intervalsNumber = pow(10, self::TEN_POWER_ROUNDING_FACTOR);
        } else {
            $intervalsNumber = $priceRange * pow($count, 1 / 3) / (3.5 * $standardDeviation);
        }
        $this->_intervalsNumber = max(ceil($intervalsNumber), self::MIN_INTERVALS_NUMBER);
        $this->_intervalsNumber = (int)min($this->_intervalsNumber, self::MAX_INTERVALS_NUMBER);

        return $this;
    }

    /**
     * Set prices model
     *
     * @param Mage_Catalog_Model_Layer_Filter_Price $pricesModel
     * @return Mage_Catalog_Model_Layer_Filter_Price_Algorithm
     */
    public function setPricesModel($pricesModel)
    {
        $this->_pricesModel = $pricesModel;
        return $this;
    }

    /**
     * Get amount of segmentation intervals
     *
     * @return int
     */
    public function getIntervalsNumber()
    {
        if (!is_null($this->_intervalsNumber)) {
            return $this->_intervalsNumber;
        }

        return 1;
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
     * Get quantile interval
     *
     * @param int $quantileNumber should be from 1 to n-1 where n is number of intervals
     * @return null|array [floatMin,floatMax]
     */
    protected function _getQuantileInterval($quantileNumber)
    {
        if ($quantileNumber < 1 || $quantileNumber >= $this->getIntervalsNumber()) {
            return null;
        }
        $quantile = $this->_getQuantile($quantileNumber);
        $deflectionLimit = floor($this->_count / 2 / $this->getIntervalsNumber());
        $limits = array(
            min(floor($quantile - $deflectionLimit), floor($quantile)),
            max(ceil($quantile + $deflectionLimit - 1), ceil($quantile)),
        );

        $deflection = self::STANDARD_NORMAL_DISTRIBUTION
            * sqrt($this->_count * $quantileNumber * ($this->getIntervalsNumber() - $quantileNumber))
            / $this->getIntervalsNumber();
        $left = max(floor($quantile - $deflection - 1), $limits[0], 0);
        if (array_key_exists($quantileNumber - 1, $this->_skippedQuantilesUpperLimits)
            && $left > $this->_skippedQuantilesUpperLimits[$quantileNumber - 1]
        ) {
            $left = $this->_skippedQuantilesUpperLimits[$quantileNumber - 1];
        }
        $right = min(ceil($quantile + $deflection), $limits[1], $this->_count - 1);
        return array($left, $right);
    }

    /**
     * Merge new round prices with old ones
     *
     * @param array $oldRoundPrices
     * @param array $newRoundPrices
     * @return void
     */
    protected function _mergeRoundPrices(&$oldRoundPrices, &$newRoundPrices)
    {
        foreach ($newRoundPrices as $roundingFactor => $roundPriceValues) {
            if (array_key_exists($roundingFactor, $oldRoundPrices)) {
                $oldRoundPrices[$roundingFactor] = array_unique(array_merge(
                    $oldRoundPrices[$roundingFactor],
                    $roundPriceValues
                ));
            } else {
                $oldRoundPrices[$roundingFactor] = $roundPriceValues;
            }
        }
    }

    /**
     * Find price separator for the quantile
     *
     * @param int $quantileNumber should be from 1 to n-1 where n is number of intervals
     * @return array|null
     */
    protected function _findPriceSeparator($quantileNumber)
    {
        if ($quantileNumber < 1 || $quantileNumber >= $this->getIntervalsNumber()) {
            return null;
        }

        $prices = array();
        $quantileInterval = $this->_getQuantileInterval($quantileNumber);
        $intervalPricesCount = $quantileInterval[1] - $quantileInterval[0] + 1;
        $offset = $quantileInterval[0];
        if (!is_null($this->_lastPriceLimiter[0])) {
            $offset -= $this->_lastPriceLimiter[0];
        }
        if ($offset < 0) {
            $intervalPricesCount += $offset;
            $prices = array_slice(
                $this->_prices,
                $this->_lastPriceLimiter[0] + $offset - $this->_quantileInterval[0],
                -$offset
            );
            $offset = 0;
        }
        $lowerPrice = $this->_lastPriceLimiter[1];
        if (!is_null($this->_lowerLimit)) {
            $lowerPrice = max($lowerPrice, $this->_lowerLimit);
        }
        if ($intervalPricesCount >= 0) {
            $prices = array_merge($prices, $this->_pricesModel->loadPrices(
                $intervalPricesCount + 1,
                $offset,
                $lowerPrice,
                $this->_upperLimit
            ));
        }
        $lastPrice = $prices[$intervalPricesCount - 1];
        $bestRoundPrice = array();
        if ($lastPrice == $prices[0]) {
            if ($quantileNumber == 1 && $offset) {
                $additionalPrices = $this->_pricesModel
                    ->loadPreviousPrices($lastPrice, $quantileInterval[0], $this->_lowerLimit);
                if ($additionalPrices) {
                    $quantileInterval[0] -= count($additionalPrices);
                    $prices = array_merge($additionalPrices, $prices);
                    $bestRoundPrice = $this->_findRoundPrice(
                        $prices[0] + Mage_Catalog_Model_Resource_Layer_Filter_Price::MIN_POSSIBLE_PRICE / 10,
                        $lastPrice,
                        false
                    );
                }
            }
            if ($quantileNumber == $this->getIntervalsNumber() - 1) {
                $pricesCount = count($prices);
                if ($prices[$pricesCount - 1] > $lastPrice) {
                    $additionalPrices = array($prices[$pricesCount - 1]);
                } else {
                    $additionalPrices = $this->_pricesModel->loadNextPrices(
                        $lastPrice,
                        $this->_count - $quantileInterval[0] - count($prices),
                        $this->_upperLimit
                    );
                }
                if ($additionalPrices) {
                    $quantileInterval[1] = $quantileInterval[0] + count($prices) - 1;
                    if ($prices[$pricesCount - 1] <= $lastPrice) {
                        $quantileInterval[1] += count($additionalPrices);
                        $prices = array_merge($prices, $additionalPrices);
                    }
                    $upperBestRoundPrice = $this->_findRoundPrice(
                        $lastPrice + Mage_Catalog_Model_Resource_Layer_Filter_Price::MIN_POSSIBLE_PRICE / 10,
                        $prices[count($prices) - 1],
                        false
                    );
                    $this->_mergeRoundPrices($bestRoundPrice, $upperBestRoundPrice);
                }
            }
        } else {
            $bestRoundPrice = $this->_findRoundPrice(
                $prices[0] + Mage_Catalog_Model_Resource_Layer_Filter_Price::MIN_POSSIBLE_PRICE / 10,
                $lastPrice
            );
        }

        $this->_quantileInterval = $quantileInterval;
        $this->_prices = $prices;

        if (empty($bestRoundPrice)) {
            $this->_skippedQuantilesUpperLimits[$quantileNumber] = $quantileInterval[1];
            return $bestRoundPrice;
        }

        $pricesCount = count($prices);
        if ($prices[$pricesCount - 1] > $lastPrice) {
            $this->_lastPriceLimiter = array($quantileInterval[0] + $pricesCount - 1, $prices[$pricesCount - 1]);
        }

        ksort($bestRoundPrice, SORT_NUMERIC);
        foreach ($bestRoundPrice as $index => &$bestRoundPriceValues) {
            if (empty($bestRoundPriceValues)) {
                unset($bestRoundPrice[$index]);
            } else {
                sort($bestRoundPriceValues);
            }
        }
        return array_reverse($bestRoundPrice);
    }

    /**
     * Find max rounding factor with given price range
     *
     * @param float $lowerPrice
     * @param float $upperPrice
     * @param bool $returnEmpty whether empty result is acceptable
     * @param null|float $roundingFactor if given, checks for range to contain the factor
     * @return false|array
     */
    protected function _findRoundPrice($lowerPrice, $upperPrice, $returnEmpty = true, $roundingFactor = null)
    {
        $lowerPrice = round($lowerPrice, 3);
        $upperPrice = round($upperPrice, 3);

        if (!is_null($roundingFactor)) {
            // Can't separate if prices are equal
            if ($lowerPrice >= $upperPrice) {
                if ($lowerPrice > $upperPrice || $returnEmpty) {
                    return false;
                }
            }
            // round is used for such examples: (1194.32 / 0.02) or (5 / 100000)
            $lowerDivision = ceil(round($lowerPrice / $roundingFactor, self::TEN_POWER_ROUNDING_FACTOR + 3));
            $upperDivision = floor(round($upperPrice / $roundingFactor, self::TEN_POWER_ROUNDING_FACTOR + 3));

            $result = array();
            if ($upperDivision <= 0 || $upperDivision - $lowerDivision > 10) {
                return $result;
            }

            for ($i = $lowerDivision; $i <= $upperDivision; ++$i) {
                $result[] = round($i * $roundingFactor, 2);
            }

            return $result;
        }

        $result = array();
        $tenPower = pow(10, self::TEN_POWER_ROUNDING_FACTOR);
        $roundingFactorCoefficients = array(10, 5, 2);
        while ($tenPower >= Mage_Catalog_Model_Resource_Layer_Filter_Price::MIN_POSSIBLE_PRICE) {
            if ($tenPower == Mage_Catalog_Model_Resource_Layer_Filter_Price::MIN_POSSIBLE_PRICE) {
                $roundingFactorCoefficients[] = 1;
            }
            foreach ($roundingFactorCoefficients as $roundingFactorCoefficient) {
                $roundingFactorCoefficient *= $tenPower;
                $roundPrices = $this->_findRoundPrice(
                    $lowerPrice, $upperPrice, $returnEmpty, $roundingFactorCoefficient
                );
                if ($roundPrices) {
                    $index = round($roundingFactorCoefficient
                        / Mage_Catalog_Model_Resource_Layer_Filter_Price::MIN_POSSIBLE_PRICE);
                    $result[$index] = $roundPrices;
                }
            }
            $tenPower /= 10;
        }

        return empty($result) ? array(1 => array()) : $result;
    }

    /**
     * Get separator nearest to quantile among the separators
     *
     * @param int $quantileNumber
     * @param array $separators
     * @return bool|array [deflection, separatorPrice, $priceIndex]
     */
    protected function _findBestSeparator($quantileNumber, $separators)
    {
        $result = false;

        $i = 0;
        $pricesCount = count($this->_prices);
        while ($i < $pricesCount && !empty($separators)) {
            $i = $this->_binarySearch($separators[0], array($i));
            if ($i == -1) {
                break;
            }

            $separator = array_shift($separators);

            $deflection = abs($quantileNumber * $this->_count
                - ($this->_quantileInterval[0] + $i) * $this->_getCalculatedIntervalsNumber());
            if (!$result || $deflection < $result[0]) {
                $result = array($deflection, $separator, $i);
            }
        }

        return $result ? $result : false;
    }

    /**
     * Calculate separators, each contains 'from', 'to' and 'count'
     *
     * @return array
     */
    public function calculateSeparators()
    {
        $result = array();
        $lastCount = 0;
        $intervalFirstPrice = $this->_minPrice;
        $lastSeparator = is_null($this->_lowerLimit) ? 0 : $this->_lowerLimit;

        for ($i = 1; $i < $this->getIntervalsNumber(); ++$i) {
            $separator = $this->_findPriceSeparator($i);
            if (empty($separator)) {
                continue;
            }
            if ($this->_quantileInterval[0] == 0) {
                $intervalFirstPrice = $this->_prices[0];
            }
            $separatorCandidate = false;
            $newIntervalFirstPrice = $intervalFirstPrice;
            $newLastSeparator = $lastSeparator;

            $pricesPerInterval = $this->_count / $this->_getCalculatedIntervalsNumber();
            while (!empty($separator) && !array_key_exists($i, $result)) {
                $separatorsPortion = array_shift($separator);
                $bestSeparator = $this->_findBestSeparator($i, $separatorsPortion);
                if ($bestSeparator && $bestSeparator[2] > 0) {
                    $isEqualPrice = ($intervalFirstPrice == $this->_prices[$bestSeparator[2] - 1])
                        ? $this->_prices[0]
                        : false;
                    $count = $bestSeparator[2] + $this->_quantileInterval[0] - $lastCount;
                    $separatorData = array(
                        'from'  => ($isEqualPrice !== false) ? $isEqualPrice : $lastSeparator,
                        'to'    => ($isEqualPrice !== false) ? $isEqualPrice : $bestSeparator[1],
                        'count' => $count,
                    );
                    if (abs(1 - $count / $pricesPerInterval) <= self::INTERVAL_DEFLECTION_LIMIT) {
                        $newLastSeparator = $bestSeparator[1];
                        $newIntervalFirstPrice = $this->_prices[$bestSeparator[2]];
                        $result[$i] = $separatorData;
                    } elseif (!$separatorCandidate || $bestSeparator[0] < $separatorCandidate[0]) {
                        $separatorCandidate = array(
                            $bestSeparator[0],
                            $separatorData,
                            $bestSeparator[1],
                            $this->_prices[$bestSeparator[2]]
                        );
                    }
                }
            }

            if (!array_key_exists($i, $result) && $separatorCandidate) {
                $newLastSeparator = $separatorCandidate[2];
                $newIntervalFirstPrice = $separatorCandidate[3];
                $result[$i] = $separatorCandidate[1];
            }

            if (array_key_exists($i, $result)) {
                $lastSeparator = $newLastSeparator;
                $intervalFirstPrice = $newIntervalFirstPrice;
                $priceIndex = $this->_binarySearch($lastSeparator);
                $lastCount += $result[$i]['count'];
                if ($priceIndex != -1 && $lastSeparator > $this->_lastPriceLimiter[1]) {
                    $this->_lastPriceLimiter = array($priceIndex + $this->_quantileInterval[0], $lastSeparator);
                }
            }
        }
        if ($this->_lastPriceLimiter[0] < $this->_count) {
            $isEqualPrice = ($intervalFirstPrice == $this->_maxPrice) ? $intervalFirstPrice : false;
            $result[$this->getIntervalsNumber()] = array(
                'from'  => $isEqualPrice ? $isEqualPrice : $lastSeparator,
                'to'    => $isEqualPrice ? $isEqualPrice : (is_null($this->_upperLimit) ? '' : $this->_upperLimit),
                'count' => $this->_count - $lastCount,
            );
        }

        return array_values($result);
    }
}
