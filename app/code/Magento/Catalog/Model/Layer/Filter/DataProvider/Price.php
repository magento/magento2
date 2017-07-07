<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Layer\Filter\DataProvider;

use Magento\Catalog\Model\Layer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;

class Price
{
    /**
     * XML configuration paths for Price Layered Navigation
     */
    const XML_PATH_RANGE_CALCULATION = 'catalog/layered_navigation/price_range_calculation';

    const XML_PATH_RANGE_STEP = 'catalog/layered_navigation/price_range_step';

    const XML_PATH_RANGE_MAX_INTERVALS = 'catalog/layered_navigation/price_range_max_intervals';

    const XML_PATH_ONE_PRICE_INTERVAL = 'catalog/layered_navigation/one_price_interval';

    const XML_PATH_INTERVAL_DIVISION_LIMIT = 'catalog/layered_navigation/interval_division_limit';

    /**
     * Price layered navigation modes: Automatic (equalize price ranges), Automatic (equalize product counts), Manual
     */
    const RANGE_CALCULATION_AUTO = 'auto';

    const RANGE_CALCULATION_IMPROVED = 'improved';

    const RANGE_CALCULATION_MANUAL = 'manual';

    /**
     * Minimal size of the range
     */
    const MIN_RANGE_POWER = 10;

    /**
     * @var Layer
     */
    private $layer;

    /**
     * @var int
     */
    private $maxPrice;

    /**
     * @var array
     */
    private $rangeItemCounts = [];

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price
     */
    private $resource;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var int
     */
    private $priceRange;

    /**
     * @var array
     */
    private $priorIntervals;

    /**
     * @var int[]
     */
    private $interval = [];

    /**
     * @param Layer $layer
     * @param Registry $coreRegistry
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price $resource
     */
    public function __construct(
        Layer $layer,
        Registry $coreRegistry,
        ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price $resource
    ) {
        $this->layer = $layer;
        $this->coreRegistry = $coreRegistry;
        $this->scopeConfig = $scopeConfig;
        $this->resource = $resource;
    }

    /**
     * @return array
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * @param array $interval
     * @return void
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;
    }

    /**
     * @return mixed
     */
    public function getRangeCalculationValue()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RANGE_CALCULATION,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getRangeStepValue()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RANGE_STEP,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getOnePriceIntervalValue()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ONE_PRICE_INTERVAL,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get interval division limit
     *
     * @return int
     */
    public function getIntervalDivisionLimitValue()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_INTERVAL_DIVISION_LIMIT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get maximum number of intervals
     *
     * @return mixed
     */
    public function getRangeMaxIntervalsValue()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RANGE_MAX_INTERVALS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return Layer
     */
    public function getLayer()
    {
        return $this->layer;
    }

    /**
     * Get price range for building filter steps
     *
     * @return int
     */
    public function getPriceRange()
    {
        $range = $this->priceRange;
        if (!$range) {
            $currentCategory = $this->coreRegistry->registry('current_category_filter');
            if ($currentCategory) {
                $range = $currentCategory->getFilterPriceRange();
            } else {
                $range = $this->getLayer()
                    ->getCurrentCategory()
                    ->getFilterPriceRange();
            }

            if (!$range) {
                $maxPrice = $this->getMaxPrice();
                $calculation = $this->getRangeCalculationValue();
                if ($calculation == self::RANGE_CALCULATION_AUTO) {
                    $index = 1;
                    do {
                        $range = pow(10, strlen(floor($maxPrice)) - $index);
                        $items = $this->getRangeItemCounts($range);
                        $index++;
                    } while ($range > self::MIN_RANGE_POWER && count($items) < 2);
                } else {
                    $range = (double)$this->getRangeStepValue();
                }
            }

            $this->priceRange = $range;
        }

        return $range;
    }

    /**
     * Get information about products count in range
     *
     * @param   int $range
     * @return  int
     */
    public function getRangeItemCounts($range)
    {
        $items = array_key_exists($range, $this->rangeItemCounts) ? $this->rangeItemCounts[$range] : null;
        if ($items === null) {
            $items = $this->resource->getCount($range);
            // checking max number of intervals
            $i = 0;
            $lastIndex = null;
            $maxIntervalsNumber = $this->getRangeMaxIntervalsValue();
            $calculation = $this->getRangeCalculationValue();
            foreach ($items as $k => $v) {
                ++$i;
                if ($calculation == self::RANGE_CALCULATION_MANUAL && $i > 1 && $i > $maxIntervalsNumber) {
                    $items[$lastIndex] += $v;
                    unset($items[$k]);
                } else {
                    $lastIndex = $k;
                }
            }
            $this->rangeItemCounts[$range] = $items;
        }

        return $items;
    }

    /**
     * Get maximum price from layer products set
     *
     * @return float
     */
    public function getMaxPrice()
    {
        $maxPrice = $this->maxPrice;
        if ($maxPrice === null) {
            $maxPrice = $this->getLayer()
                ->getProductCollection()
                ->getMaxPrice();
            $maxPrice = floor($maxPrice);
            $this->maxPrice = $maxPrice;
        }

        return $maxPrice;
    }

    /**
     * @param string $filterParams
     * @return array
     */
    public function getPriorFilters($filterParams)
    {
        $priorFilters = [];
        for ($i = 1; $i < count($filterParams); ++$i) {
            $priorFilter = $this->validateFilter($filterParams[$i]);
            if ($priorFilter) {
                $priorFilters[] = $priorFilter;
            } else {
                //not valid data
                $priorFilters = [];
                break;
            }
        }

        return $priorFilters;
    }

    /**
     * Validate and parse filter request param
     *
     * @param string $filter
     * @return array|bool
     */
    public function validateFilter($filter)
    {
        $filter = explode('-', $filter);
        if (count($filter) != 2) {
            return false;
        }
        foreach ($filter as $v) {
            if ($v !== '' && $v !== '0' && (double)$v <= 0 || is_infinite((double)$v)) {
                return false;
            }
        }

        return $filter;
    }

    /**
     * Get filter value for reset current filter state
     *
     * @return null|string
     */
    public function getResetValue()
    {
        $priorIntervals = $this->getPriorIntervals();
        $value = [];
        if ($priorIntervals) {
            foreach ($priorIntervals as $priorInterval) {
                $value[] = implode('-', $priorInterval);
            }

            return implode(',', $value);
        }

        return null;
    }

    /**
     * @return array
     */
    public function getPriorIntervals()
    {
        return $this->priorIntervals;
    }

    /**
     * @param array $priorInterval
     * @return void
     */
    public function setPriorIntervals($priorInterval)
    {
        $this->priorIntervals = $priorInterval;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return string
     */
    public function getAdditionalRequestData()
    {
        $result = '';
        $appliedInterval = $this->getInterval();
        if ($appliedInterval) {
            $result = ',' . $appliedInterval[0] . '-' . $appliedInterval[1];
            $priorIntervals = $this->getResetValue();
            if ($priorIntervals) {
                $result .= ',' . $priorIntervals;
            }
        }

        return $result;
    }
}
