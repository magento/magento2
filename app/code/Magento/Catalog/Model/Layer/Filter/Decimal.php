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


/**
 * Catalog Layer Decimal Attribute Filter Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Layer\Filter;

class Decimal extends \Magento\Catalog\Model\Layer\Filter\AbstractFilter
{
    const MIN_RANGE_POWER = 10;

    /**
     * Resource instance
     *
     * @var \Magento\Catalog\Model\Resource\Layer\Filter\Decimal
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param ItemFactory $filterItemFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Resource\Layer\Filter\DecimalFactory $filterDecimalFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Resource\Layer\Filter\DecimalFactory $filterDecimalFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = array()
    ) {
        $this->_resource = $filterDecimalFactory->create();
        $this->_requestVar = 'decimal';
        $this->priceCurrency = $priceCurrency;
        parent::__construct($filterItemFactory, $storeManager, $layer, $data);
    }

    /**
     * Retrieve resource instance
     *
     * @return \Magento\Catalog\Model\Resource\Layer\Filter\Decimal
     */
    protected function _getResource()
    {
        return $this->_resource;
    }

    /**
     * Apply decimal range filter to product collection
     *
     * @param \Zend_Controller_Request_Abstract $request
     * @return $this
     */
    public function apply(\Zend_Controller_Request_Abstract $request)
    {
        parent::apply($request);

        /**
         * Filter must be string: $index, $range
         */
        $filter = $request->getParam($this->getRequestVar());
        if (!$filter || is_array($filter)) {
            return $this;
        }

        $filter = explode(',', $filter);
        if (count($filter) != 2) {
            return $this;
        }

        list($index, $range) = $filter;
        if ((int)$index && (int)$range) {
            $this->setRange((int)$range);

            $this->_getResource()->applyFilterToCollection($this, $range, $index);
            $this->getLayer()->getState()->addFilter(
                $this->_createItem($this->_renderItemLabel($range, $index), $filter)
            );

            $this->_items = array();
        }

        return $this;
    }

    /**
     * Retrieve price aggreagation data cache key
     *
     * @return string
     */
    protected function _getCacheKey()
    {
        $key = $this->getLayer()->getStateKey() . '_ATTR_' . $this->getAttributeModel()->getAttributeCode();
        return $key;
    }

    /**
     * Prepare text of item label
     *
     * @param   int $range
     * @param   float $value
     * @return  string
     */
    protected function _renderItemLabel($range, $value)
    {
        $from = $this->priceCurrency->format(($value - 1) * $range, false);
        $to = $this->priceCurrency->format($value * $range, false);
        return __('%1 - %2', $from, $to);
    }

    /**
     * Retrieve maximum value from layer products set
     *
     * @return float
     */
    public function getMaxValue()
    {
        $max = $this->getData('max_value');
        if (is_null($max)) {
            list($min, $max) = $this->_getResource()->getMinMax($this);
            $this->setData('max_value', $max);
            $this->setData('min_value', $min);
        }
        return $max;
    }

    /**
     * Retrieve minimal value from layer products set
     *
     * @return float
     */
    public function getMinValue()
    {
        $min = $this->getData('min_value');
        if (is_null($min)) {
            list($min, $max) = $this->_getResource()->getMinMax($this);
            $this->setData('max_value', $max);
            $this->setData('min_value', $min);
        }
        return $min;
    }

    /**
     * Retrieve range for building filter steps
     *
     * @return int
     */
    public function getRange()
    {
        $range = $this->getData('range');
        if (!$range) {
            $maxValue = $this->getMaxValue();
            $index = 1;
            do {
                $range = pow(10, strlen(floor($maxValue)) - $index);
                $items = $this->getRangeItemCounts($range);
                $index++;
            } while ($range > self::MIN_RANGE_POWER && count($items) < 2);
            $this->setData('range', $range);
        }

        return $range;
    }

    /**
     * Retrieve information about products count in range
     *
     * @param int $range
     * @return int
     */
    public function getRangeItemCounts($range)
    {
        $rangeKey = 'range_item_counts_' . $range;
        $items = $this->getData($rangeKey);
        if (is_null($items)) {
            $items = $this->_getResource()->getCount($this, $range);
            $this->setData($rangeKey, $items);
        }
        return $items;
    }

    /**
     * Retrieve data for build decimal filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        $data = array();
        $range = $this->getRange();
        $dbRanges = $this->getRangeItemCounts($range);

        foreach ($dbRanges as $index => $count) {
            $data[] = array(
                'label' => $this->_renderItemLabel($range, $index),
                'value' => $index . ',' . $range,
                'count' => $count
            );
        }

        return $data;
    }
}
