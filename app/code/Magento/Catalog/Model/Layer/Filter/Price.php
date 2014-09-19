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
namespace Magento\Catalog\Model\Layer\Filter;

/**
 * Layer price filter
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Price extends \Magento\Catalog\Model\Layer\Filter\AbstractFilter
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

    // equalize price ranges
    const RANGE_CALCULATION_IMPROVED = 'improved';

    // equalize product counts
    const RANGE_CALCULATION_MANUAL = 'manual';

    /**
     * Minimal size of the range
     */
    const MIN_RANGE_POWER = 10;

    /**
     * Resource instance
     *
     * @var \Magento\Catalog\Model\Resource\Layer\Filter\Price
     */
    protected $_resource;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Catalog layer filter price algorithm
     *
     * @var \Magento\Catalog\Model\Layer\Filter\Price\Algorithm
     */
    protected $_priceAlgorithm;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param ItemFactory $filterItemFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Resource\Layer\Filter\PriceFactory $filterPriceFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param Price\Algorithm $priceAlgorithm
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Resource\Layer\Filter\PriceFactory $filterPriceFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\Layer\Filter\Price\Algorithm $priceAlgorithm,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = array()
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->_resource = $filterPriceFactory->create();
        $this->_customerSession = $customerSession;
        $this->_priceAlgorithm = $priceAlgorithm;
        $this->_coreRegistry = $coreRegistry;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($filterItemFactory, $storeManager, $layer, $data);
        $this->_requestVar = 'price';
    }

    /**
     * Retrieve resource instance
     *
     * @return \Magento\Catalog\Model\Resource\Layer\Filter\Price
     */
    protected function _getResource()
    {
        return $this->_resource;
    }

    /**
     * Get price range for building filter steps
     *
     * @return int
     */
    public function getPriceRange()
    {
        $range = $this->getData('price_range');
        if (!$range) {
            $currentCategory = $this->_coreRegistry->registry('current_category_filter');
            if ($currentCategory) {
                $range = $currentCategory->getFilterPriceRange();
            } else {
                $range = $this->getLayer()->getCurrentCategory()->getFilterPriceRange();
            }

            $maxPrice = $this->getMaxPriceInt();
            if (!$range) {
                $calculation = $this->_scopeConfig->getValue(
                    self::XML_PATH_RANGE_CALCULATION,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
                if ($calculation == self::RANGE_CALCULATION_AUTO) {
                    $index = 1;
                    do {
                        $range = pow(10, strlen(floor($maxPrice)) - $index);
                        $items = $this->getRangeItemCounts($range);
                        $index++;
                    } while ($range > self::MIN_RANGE_POWER && count($items) < 2);
                } else {
                    $range = (double)$this->_scopeConfig->getValue(
                        self::XML_PATH_RANGE_STEP,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );
                }
            }

            $this->setData('price_range', $range);
        }

        return $range;
    }

    /**
     * Get maximum price from layer products set
     *
     * @return float
     */
    public function getMaxPriceInt()
    {
        $maxPrice = $this->getData('max_price_int');
        if (is_null($maxPrice)) {
            $maxPrice = $this->getLayer()->getProductCollection()->getMaxPrice();
            $maxPrice = floor($maxPrice);
            $this->setData('max_price_int', $maxPrice);
        }

        return $maxPrice;
    }

    /**
     * Get information about products count in range
     *
     * @param   int $range
     * @return  int
     */
    public function getRangeItemCounts($range)
    {
        $rangeKey = 'range_item_counts_' . $range;
        $items = $this->getData($rangeKey);
        if (is_null($items)) {
            $items = $this->_getResource()->getCount($this, $range);
            // checking max number of intervals
            $i = 0;
            $lastIndex = null;
            $maxIntervalsNumber = $this->getMaxIntervalsNumber();
            $calculation = $this->_scopeConfig->getValue(
                self::XML_PATH_RANGE_CALCULATION,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            foreach ($items as $k => $v) {
                ++$i;
                if ($calculation == self::RANGE_CALCULATION_MANUAL && $i > 1 && $i > $maxIntervalsNumber) {
                    $items[$lastIndex] += $v;
                    unset($items[$k]);
                } else {
                    $lastIndex = $k;
                }
            }
            $this->setData($rangeKey, $items);
        }

        return $items;
    }

    /**
     * Prepare text of item label
     *
     * @param      int $range
     * @param      float $value
     * @return     string
     * @deprecated since 1.7.0.0
     */
    protected function _renderItemLabel($range, $value)
    {
        $fromPrice = $this->priceCurrency->format(($value - 1) * $range);
        $toPrice = $this->priceCurrency->format($value * $range);

        return __('%1 - %2', $fromPrice, $toPrice);
    }

    /**
     * Prepare text of range label
     *
     * @param float|string $fromPrice
     * @param float|string $toPrice
     * @return string
     */
    protected function _renderRangeLabel($fromPrice, $toPrice)
    {
        $formattedFromPrice = $this->priceCurrency->format($fromPrice);
        if ($toPrice === '') {
            return __('%1 and above', $formattedFromPrice);
        } elseif ($fromPrice == $toPrice && $this->_scopeConfig->getValue(
            self::XML_PATH_ONE_PRICE_INTERVAL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )
        ) {
            return $formattedFromPrice;
        } else {
            if ($fromPrice != $toPrice) {
                $toPrice -= .01;
            }
            return __('%1 - %2', $formattedFromPrice, $this->priceCurrency->format($toPrice));
        }
    }

    /**
     * Get additional request param data
     *
     * @return string
     */
    protected function _getAdditionalRequestData()
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

    /**
     * Get data generated by algorithm for build price filter items
     *
     * @return array
     */
    protected function _getCalculatedItemsData()
    {
        $collection = $this->getLayer()->getProductCollection();
        $appliedInterval = $this->getInterval();
        if ($appliedInterval && $collection->getPricesCount() <= $this->getIntervalDivisionLimit()) {
            return array();
        }
        $this->_priceAlgorithm->setPricesModel(
            $this
        )->setStatistics(
            $collection->getMinPrice(),
            $collection->getMaxPrice(),
            $collection->getPriceStandardDeviation(),
            $collection->getPricesCount()
        );

        if ($appliedInterval) {
            if ($appliedInterval[0] == $appliedInterval[1] || $appliedInterval[1] === '0') {
                return array();
            }
            $this->_priceAlgorithm->setLimits($appliedInterval[0], $appliedInterval[1]);
        }

        $items = array();
        foreach ($this->_priceAlgorithm->calculateSeparators() as $separator) {
            $items[] = array(
                'label' => $this->_renderRangeLabel($separator['from'], $separator['to']),
                'value' => ($separator['from'] ==
                0 ? '' : $separator['from']) . '-' . $separator['to'] . $this->_getAdditionalRequestData(),
                'count' => $separator['count']
            );
        }

        return $items;
    }

    /**
     * Get data for build price filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        if ($this->_scopeConfig->getValue(
            self::XML_PATH_RANGE_CALCULATION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) == self::RANGE_CALCULATION_IMPROVED
        ) {
            return $this->_getCalculatedItemsData();
        } elseif ($this->getInterval()) {
            return array();
        }

        $range = $this->getPriceRange();
        $dbRanges = $this->getRangeItemCounts($range);
        $data = array();

        if (!empty($dbRanges)) {
            $lastIndex = array_keys($dbRanges);
            $lastIndex = $lastIndex[count($lastIndex) - 1];

            foreach ($dbRanges as $index => $count) {
                $fromPrice = $index == 1 ? '' : ($index - 1) * $range;
                $toPrice = $index == $lastIndex ? '' : $index * $range;

                $data[] = array(
                    'label' => $this->_renderRangeLabel($fromPrice, $toPrice),
                    'value' => $fromPrice . '-' . $toPrice,
                    'count' => $count
                );
            }
        }

        return $data;
    }

    /**
     * Apply price range filter to collection
     *
     * @return $this
     */
    protected function _applyPriceRange()
    {
        $this->_getResource()->applyPriceRange($this);
        return $this;
    }

    /**
     * Validate and parse filter request param
     *
     * @param string $filter
     * @return array|bool
     */
    protected function _validateFilter($filter)
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
     * Apply price range filter
     *
     * @param \Zend_Controller_Request_Abstract $request
     * @return $this
     */
    public function apply(\Zend_Controller_Request_Abstract $request)
    {
        /**
         * Filter must be string: $fromPrice-$toPrice
         */
        $filter = $request->getParam($this->getRequestVar());
        if (!$filter || is_array($filter)) {
            return $this;
        }

        //validate filter
        $filterParams = explode(',', $filter);
        $filter = $this->_validateFilter($filterParams[0]);
        if (!$filter) {
            return $this;
        }

        list($from, $to) = $filter;

        $this->setInterval(array($from, $to));

        $priorFilters = array();
        for ($i = 1; $i < count($filterParams); ++$i) {
            $priorFilter = $this->_validateFilter($filterParams[$i]);
            if ($priorFilter) {
                $priorFilters[] = $priorFilter;
            } else {
                //not valid data
                $priorFilters = array();
                break;
            }
        }
        if ($priorFilters) {
            $this->setPriorIntervals($priorFilters);
        }

        $this->_applyPriceRange();
        $this->getLayer()->getState()->addFilter(
            $this->_createItem($this->_renderRangeLabel(empty($from) ? 0 : $from, $to), $filter)
        );

        return $this;
    }

    /**
     * Apply filter value to product collection based on filter range and selected value
     *
     * @param int $range
     * @param int $index
     * @return \Magento\Catalog\Model\Layer\Filter\Price
     * @deprecated since 1.7.0.0
     */
    protected function _applyToCollection($range, $index)
    {
        $this->_getResource()->applyFilterToCollection($this, $range, $index);
        return $this;
    }

    /**
     * Retrieve active customer group id
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        $customerGroupId = $this->_getData('customer_group_id');
        if (is_null($customerGroupId)) {
            $customerGroupId = $this->_customerSession->getCustomerGroupId();
        }
        return $customerGroupId;
    }

    /**
     * Set active customer group id for filter
     *
     * @param int $customerGroupId
     * @return $this
     */
    public function setCustomerGroupId($customerGroupId)
    {
        return $this->setData('customer_group_id', $customerGroupId);
    }

    /**
     * Retrieve active currency rate for filter
     *
     * @return float
     */
    public function getCurrencyRate()
    {
        $rate = $this->_getData('currency_rate');
        if (is_null($rate)) {
            $rate = $this->_storeManager->getStore($this->getStoreId())->getCurrentCurrencyRate();
        }
        if (!$rate) {
            $rate = 1;
        }
        return $rate;
    }

    /**
     * Set active currency rate for filter
     *
     * @param float $rate
     * @return $this
     */
    public function setCurrencyRate($rate)
    {
        return $this->setData('currency_rate', $rate);
    }

    /**
     * Get maximum number of intervals
     *
     * @return int
     */
    public function getMaxIntervalsNumber()
    {
        return (int)$this->_scopeConfig->getValue(
            self::XML_PATH_RANGE_MAX_INTERVALS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get interval division limit
     *
     * @return int
     */
    public function getIntervalDivisionLimit()
    {
        return (int)$this->_scopeConfig->getValue(
            self::XML_PATH_INTERVAL_DIVISION_LIMIT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get filter value for reset current filter state
     *
     * @return null|string
     */
    public function getResetValue()
    {
        $priorIntervals = $this->getPriorIntervals();
        $value = array();
        if ($priorIntervals) {
            foreach ($priorIntervals as $priorInterval) {
                $value[] = implode('-', $priorInterval);
            }
            return implode(',', $value);
        }
        return parent::getResetValue();
    }

    /**
     * Get 'clear price' link text
     *
     * @return false|string
     */
    public function getClearLinkText()
    {
        if ($this->_scopeConfig->getValue(
            self::XML_PATH_RANGE_CALCULATION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) == self::RANGE_CALCULATION_IMPROVED && $this->getPriorIntervals()
        ) {
            return __('Clear Price');
        }

        return parent::getClearLinkText();
    }

    /**
     * Load range of product prices
     *
     * @param int $limit
     * @param null|int $offset
     * @param null|int $lowerPrice
     * @param null|int $upperPrice
     * @return array
     */
    public function loadPrices($limit, $offset = null, $lowerPrice = null, $upperPrice = null)
    {
        $prices = $this->_getResource()->loadPrices($this, $limit, $offset, $lowerPrice, $upperPrice);
        if ($prices) {
            $prices = array_map('floatval', $prices);
        }

        return $prices;
    }

    /**
     * Load range of product prices, preceding the price
     *
     * @param float $price
     * @param int $index
     * @param null|int $lowerPrice
     * @return array|false
     */
    public function loadPreviousPrices($price, $index, $lowerPrice = null)
    {
        $prices = $this->_getResource()->loadPreviousPrices($this, $price, $index, $lowerPrice);
        if ($prices) {
            $prices = array_map('floatval', $prices);
        }

        return $prices;
    }

    /**
     * Load range of product prices, next to the price
     *
     * @param float $price
     * @param int $rightIndex
     * @param null|int $upperPrice
     * @return array|false
     */
    public function loadNextPrices($price, $rightIndex, $upperPrice = null)
    {
        $prices = $this->_getResource()->loadNextPrices($this, $price, $rightIndex, $upperPrice);
        if ($prices) {
            $prices = array_map('floatval', $prices);
        }

        return $prices;
    }
}
