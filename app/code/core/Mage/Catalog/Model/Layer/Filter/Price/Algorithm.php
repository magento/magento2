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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
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
    const MIN_POSSIBLE_PRICE = .01;
    const TEN_POWER_ROUNDING_FACTOR = 4;

    /**
     * Sorted array of all products prices
     *
     * @var array
     */
    protected $_prices = null;

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
     * Set products prices
     *
     * @param array $prices
     * @return Mage_Catalog_Model_Layer_Filter_Price_Algorithm
     */
    public function setPrices(array $prices)
    {
        $this->_prices = $prices;
        sort($this->_prices);
        $this->_intervalsNumber = null;
        $this->_skippedQuantilesUpperLimits = array();

        return $this;
    }

    /**
     * Get min price
     *
     * @return float
     */
    public function getMinPrice()
    {
        return empty($this->_prices) ? 0 : $this->_prices[0];
    }

    /**
     * Get max price
     *
     * @return float
     */
    public function getMaxPrice()
    {
        return (empty($this->_prices)) ? 0 : $this->_prices[count($this->_prices) - 1];
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

        $pricesCount = count($this->_prices);
        if ($pricesCount < 2 || ($this->getMaxPrice() - $this->getMinPrice() == 0)) {
            //Same price couldn't be separated with several intervals
            $this->_intervalsNumber = 1;
            return $this->_intervalsNumber;
        }

        $sum = 0;
        $sumSquares = 0;
        foreach ($this->_prices as $price) {
            $sum += $price;
            $sumSquares += $price * $price;
        }

        if ($pricesCount * $sumSquares - $sum * $sum == 0) {
            $intervalsNumber = 1000;
        } else {
            $intervalsNumber = ($this->getMaxPrice() - $this->getMinPrice()) * pow($pricesCount, 5 / 6)
                * sqrt(($pricesCount - 1) / ($pricesCount * $sumSquares - $sum * $sum)) / 3.5;
        }
        $this->_intervalsNumber = min(max(ceil($intervalsNumber), 2), 10);

        return $this->_intervalsNumber;
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

        return $quantileNumber * count($this->_prices) / $this->getIntervalsNumber() - .5;
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
        $pricesCount = count($this->_prices);
        $quantile = $this->_getQuantile($quantileNumber);
        $deflectionLimit = floor($pricesCount / 2 / $this->getIntervalsNumber());
        $limits = array(
            min(floor($quantile - $deflectionLimit), floor($quantile)),
            max(ceil($quantile + $deflectionLimit - 1), ceil($quantile)),
        );

        $deflection = $this->_getStandardNormalDistribution()
            * sqrt($pricesCount * $quantileNumber * ($this->getIntervalsNumber() - $quantileNumber))
            / $this->getIntervalsNumber();
        $left = max(floor($quantile - $deflection - 1), $limits[0], 0);
        if (array_key_exists($quantileNumber - 1, $this->_skippedQuantilesUpperLimits)
            && $left > $this->_skippedQuantilesUpperLimits[$quantileNumber - 1]
        ) {
            $left = $this->_skippedQuantilesUpperLimits[$quantileNumber - 1];
        }
        $right = min(ceil($quantile + $deflection), $limits[1], $pricesCount - 1);
        return array($left, $right);
     }

    /**
     * Get standard normal distribution
     *
     * @return float
     */
    protected function _getStandardNormalDistribution()
    {
        return 1.96;
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
            if ($lowerDivision > $upperDivision) {
                return false;
            }
            $averageDivision = ($lowerDivision + $upperDivision) / 2;
            $lowerAverageDivision = floor($averageDivision);
            $result = array(round($lowerAverageDivision * $roundingFactor, 2));
            if ($averageDivision != $lowerAverageDivision) {
                $upperAverageDivision = ceil($averageDivision);
                $result[] = round($upperAverageDivision * $roundingFactor, 2);
            }
            return $result;
        }

        $tenPower = pow(10, self::TEN_POWER_ROUNDING_FACTOR);
        $roundingFactorCoefficients = array(10, 5, 2);
        while ($tenPower >= self::MIN_POSSIBLE_PRICE) {
            if ($tenPower == self::MIN_POSSIBLE_PRICE) {
                $roundingFactorCoefficients[] = 1;
            }
            foreach ($roundingFactorCoefficients as $roundingFactorCoefficient) {
                $roundingFactorCoefficient *= $tenPower;
                $roundPrices = $this->_findRoundPrice(
                    $lowerPrice, $upperPrice, $returnEmpty, $roundingFactorCoefficient
                );
                if ($roundPrices) {
                    return array($roundingFactorCoefficient, $roundPrices);
                }
            }
            $tenPower /= 10;
        }

        return array(self::MIN_POSSIBLE_PRICE, array());
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
        $quantile = $this->_getQuantile($quantileNumber);
        $lowerQuantile = floor($quantile);
        $upperQuantile = ceil($quantile);

        $quantileInterval = $this->_getQuantileInterval($quantileNumber);
        $quantileDeflection = 0;
        $maxRoundingFactor = self::MIN_POSSIBLE_PRICE;
        $bestRoundPrice = array();

        if ($this->_prices[$quantileInterval[0]] == $this->_prices[$quantileInterval[1]]) {
            if ($quantileNumber == 1) {
                $i = $quantileInterval[0];
                while ($i >= 0 && ($this->_prices[$i] == $this->_prices[$quantileInterval[1]])) {
                    --$i;
                }
                if ($i >= 0) {
                    list($roundingFactor, $bestRoundPrice) = $this->_findRoundPrice(
                        $this->_prices[$i] + self::MIN_POSSIBLE_PRICE / 10,
                        $this->_prices[$quantileInterval[1]],
                        false
                    );
                }
            }
            if ($quantileNumber == $this->getIntervalsNumber() - 1) {
                $pricesCount = count($this->_prices);
                $i = $quantileInterval[1];
                while ($i < $pricesCount && ($this->_prices[$quantileInterval[0]] == $this->_prices[$i])) {
                    ++$i;
                }
                if ($i < $pricesCount) {
                    list($upperRoundingFactor, $upperBestRoundPrice) = $this->_findRoundPrice(
                        $this->_prices[$quantileInterval[0]] + self::MIN_POSSIBLE_PRICE / 10,
                        $this->_prices[$i],
                        false
                    );
                    if (!empty($bestRoundPrice)) {
                        if ($upperRoundingFactor >= $roundingFactor) {
                            if ($upperRoundingFactor > $roundingFactor) {
                                $bestRoundPrice = $upperBestRoundPrice;
                            } else {
                                $bestRoundPrice = array_merge($bestRoundPrice, $upperBestRoundPrice);
                            }
                        }
                    } else {
                        $bestRoundPrice = $upperBestRoundPrice;
                    }
                }
            }
        } else {
            while ($lowerQuantile - $quantileDeflection >= $quantileInterval[0]
                || $upperQuantile + $quantileDeflection <= $quantileInterval[1]
            ) {
                $leftIndex = max($quantileInterval[0], $lowerQuantile - $quantileDeflection);
                $rightIndex = min($quantileInterval[1], $upperQuantile + $quantileDeflection);

                list($roundingFactor, $roundPrice) = $this->_findRoundPrice(
                    $this->_prices[$leftIndex] + self::MIN_POSSIBLE_PRICE / 10,
                    $this->_prices[$rightIndex]
                );

                if ($roundingFactor >= $maxRoundingFactor) {
                    if ($roundingFactor == $maxRoundingFactor) {
                        $bestRoundPrice = array_unique(array_merge($bestRoundPrice, $roundPrice));
                    } else {
                        $bestRoundPrice = $roundPrice;
                        $maxRoundingFactor = $roundingFactor;
                    }
                }
                ++$quantileDeflection;
            }
        }

        if (empty($bestRoundPrice)) {
            $this->_skippedQuantilesUpperLimits[$quantileNumber] = $quantileInterval[1];
            return $bestRoundPrice;
        }

        sort($bestRoundPrice);
        return $bestRoundPrice;
    }

    /**
     * Calculate separators, each contains 'from', 'to' and 'count'
     *
     * @return array
     */
    public function calculateSeparators()
    {
        $separators = array();
        for ($i = 1; $i < $this->getIntervalsNumber(); ++$i) {
            $separators[] = $this->_findPriceSeparator($i);
        }
        $pricesCount = count($this->_prices);

        $i = 0;
        $result = array();
        $lastSeparator = 0;
        $quantile = 0;
        while (!empty($separators) && ($i < $pricesCount)) {
            while (!empty($separators) && empty($separators[0])) {
                array_shift($separators);
            }
            if (empty($separators)) {
                break;
            }
            if ($this->_prices[$i] < $separators[0][0]) {
                ++$i;
            } else {
                $separator = array_shift($separators[0]);
                $separatorData = array(
                    'from'  => $lastSeparator,
                    'to'    => $separator,
                    'count' => $i,
                );

                $deflection = abs(($quantile + 1) / $this->getIntervalsNumber() - $i / $pricesCount);
                if (!array_key_exists($quantile, $result)) {
                    $result[$quantile] = array($deflection, $separatorData);
                } elseif ($deflection < $result[$quantile][0]) {
                    $result[$quantile] = array($deflection, $separatorData);
                }

                if (empty($separators[0])) {
                    array_shift($separators);
                    if (!array_key_exists($quantile - 1, $result)
                        || $result[$quantile - 1][1]['count'] < $result[$quantile][1]['count']
                    ) {
                        $lastSeparator = $result[$quantile][1]['to'];
                    }
                    ++$quantile;
                }
            }
        }
        if ($i < $pricesCount || empty($result)) {
            $result[$quantile] = array(0, array(
                'from'  => $lastSeparator,
                'to'    => '',
                'count' => $pricesCount,
            ));
        }

        for ($i = count($result) - 1; $i >= 0; --$i) {
            $rangeCount = ($i == 0) ? $result[$i][1]['count'] : ($result[$i][1]['count'] - $result[$i-1][1]['count']);
            if ($rangeCount > 0) {
                $result[$i] = $result[$i][1];
                $firstPriceInRange = $this->_prices[$result[$i]['count'] - $rangeCount];
                if ($this->_prices[$result[$i]['count'] - 1] == $firstPriceInRange) {
                    $result[$i]['from'] = $firstPriceInRange;
                    $result[$i]['to'] = $firstPriceInRange;
                }
                $result[$i]['from'] = round($result[$i]['from'], 2);
                if (!empty($result[$i]['to'])) {
                    $result[$i]['to'] = round($result[$i]['to'], 2);
                }
                $result[$i]['count'] = $rangeCount;
            } else {
                unset($result[$i]);
            }
        }

        return array_values($result);
    }
}
