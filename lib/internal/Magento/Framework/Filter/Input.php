<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

use Exception;
use Laminas\Filter\FilterInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\ObjectManagerInterface;

/**
 * Filter data collector
 */
class Input implements FilterInterface
{
    private const CHAIN_APPEND  = 'append';
    private const CHAIN_PREPEND = 'prepend';

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Filters data collectors
     *
     * @var array
     */
    protected $_filters = [];

    /**
     * Add filter
     *
     * @param string $name
     * @param array|FilterInterface $filter
     * @param string $placement
     * @return $this
     */
    public function addFilter($name, $filter, $placement = self::CHAIN_APPEND)
    {
        if ($placement == self::CHAIN_PREPEND) {
            array_unshift($this->_filters[$name], $filter);
        } else {
            $this->_filters[$name][] = $filter;
        }
        return $this;
    }

    /**
     * Add a filter to the end of the chain
     *
     * @param FilterInterface $filter
     * @return $this
     */
    public function appendFilter(FilterInterface $filter)
    {
        return $this->addFilter('', $filter);
    }

    /**
     * Add a filter to the start of the chain
     *
     * @param  array|FilterInterface $filter
     * @return $this
     */
    public function prependFilter($filter)
    {
        return $this->addFilter('', $filter, self::CHAIN_PREPEND);
    }

    /**
     * Add filters
     *
     * Filters data must be has view as
     *      array(
     *          'key1' => $filters,
     *          'key2' => array( ... ), //array filters data
     *          'key2' => $filters
     *      )
     *
     * @param array $filters
     * @return $this
     */
    public function addFilters(array $filters)
    {
        $this->_filters = array_merge_recursive($this->_filters, $filters);
        return $this;
    }

    /**
     * Set filters
     *
     * @param array $filters
     * @return $this
     */
    public function setFilters(array $filters)
    {
        $this->_filters = $filters;
        return $this;
    }

    /**
     * Get filters
     *
     * @param string|null $name Get filter for selected name
     * @return array|FilterInterface
     */
    public function getFilters($name = null)
    {
        if (null === $name) {
            return $this->_filters;
        } else {
            return $this->_filters[$name] ?? null;
        }
    }

    /**
     * Filter data
     *
     * @param array $data
     * @return array Return filtered data
     */
    public function filter($data)
    {
        return $this->_filter($data);
    }

    /**
     * Recursive filtering
     *
     * @param array $data
     * @param array|null $filters
     * @param bool $isFilterListSimple
     * @return array
     * @throws Exception when filter is not found or not instance of defined instances
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _filter(array $data, &$filters = null, $isFilterListSimple = false)
    {
        if (null === $filters) {
            $filters = & $this->_filters;
        }
        foreach ($data as $key => $value) {
            if (!$isFilterListSimple && !empty($filters[$key])) {
                $itemFilters = $filters[$key];
            } elseif ($isFilterListSimple && !empty($filters)) {
                $itemFilters = $filters;
            } else {
                continue;
            }

            if (!$isFilterListSimple && is_array($value) && isset($filters[$key]['children_filters'])) {
                $isChildrenFilterListSimple = is_numeric(implode('', array_keys($filters[$key]['children_filters'])));
                $value = $this->_filter($value, $filters[$key]['children_filters'], $isChildrenFilterListSimple);
            } else {
                foreach ($itemFilters as $filterData) {
                    if ($laminasFilter = $this->_getLaminasFilter($filterData)) {
                        $value = $laminasFilter->filter($value);
                    } elseif ($filtrationHelper = $this->_getFiltrationHelper($filterData)) {
                        $value = $this->_applyFiltrationWithHelper($value, $filtrationHelper, $filterData);
                    }
                }
            }
            $data[$key] = $value;
        }
        return $data;
    }

    /**
     * Call specified helper method for $value filtration
     *
     * @param mixed $value
     * @param AbstractHelper $helper
     * @param array $filterData
     * @return mixed
     * @throws Exception
     */
    protected function _applyFiltrationWithHelper(
        $value,
        AbstractHelper $helper,
        array $filterData
    ) {
        if (!isset($filterData['method']) || empty($filterData['method'])) {
            throw new FilterException("Helper filtration method is not set");
        }
        if (!isset($filterData['args']) || empty($filterData['args'])) {
            $filterData['args'] = [];
        }
        $filterData['args'] = [-100 => $value] + $filterData['args'];
        // apply filter
        return call_user_func_array([$helper, $filterData['method']], $filterData['args']);
    }

    /**
     * Try to create Magento helper for filtration based on $filterData. Return false on failure
     *
     * @param FilterInterface|array $filterData
     * @return false|AbstractHelper
     * @throws Exception
     */
    protected function _getFiltrationHelper($filterData)
    {
        $helper = false;
        if (isset($filterData['helper'])) {
            $helper = $filterData['helper'];
            if (is_string($helper)) {
                $helper = $this->_objectManager->get($helper);
            } elseif (!$helper instanceof AbstractHelper) {
                throw new FilterException("Filter '{$helper}' not found");
            }
        }
        return $helper;
    }

    /**
     * Try to create Laminas filter based on $filterData. Return false on failure
     *
     * @param FilterInterface|array $filterData
     * @return false|FilterInterface
     */
    protected function _getLaminasFilter($filterData)
    {
        $laminasFilter = false;
        if ($filterData instanceof FilterInterface) {
            $laminasFilter = $filterData;
        } elseif (isset($filterData['model'])) {
            $laminasFilter = $this->_createCustomLaminasFilter($filterData);
        } elseif (isset($filterData['laminas'])) {
            $laminasFilter = $this->_createNativeLaminasFilter($filterData);
        }
        return $laminasFilter;
    }

    /**
     * Get Magento filters
     *
     * @param array $filterData
     * @return FilterInterface
     * @throws Exception
     */
    protected function _createCustomLaminasFilter($filterData)
    {
        $filter = $filterData['model'];
        if (!isset($filterData['args'])) {
            $filterData['args'] = null;
        } else {
            //use only first element because object manager cannot get more
            $filterData['args'] = $filterData['args'][0];
        }
        if (is_string($filter)) {
            $filter = $this->_objectManager->create($filter, $filterData['args']);
        }
        if (!$filter instanceof FilterInterface) {
            throw new FilterException('Filter is not instance of FilterInterface');
        }
        return $filter;
    }

    /**
     * Get native Filter
     *
     * @param array $filterData
     * @return FilterInterface
     * @throws Exception
     */
    protected function _createNativeLaminasFilter($filterData)
    {
        $filter = $filterData['laminas'];
        if (is_string($filter)) {
            $filterClassName = '\\Laminas\\Filter\\' . ucfirst($filter);
            if (!is_a($filterClassName, FilterInterface::class, true)) {
                throw new FilterException('Filter is not instance of FilterInterface');
            }
            $filterClassOptions = $filterData['args'] ?? [];
            $filter = new $filterClassName(...array_values($filterClassOptions));
        }

        return $filter;
    }
}
