<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml;

/**
 * Backend report grid block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Grid extends \Magento\Backend\Block\Widget\Grid
{
    /**
     * Should Store Switcher block be visible
     *
     * @var bool
     */
    protected $_storeSwitcherVisibility = true;

    /**
     * Should Date Filter block be visible
     *
     * @var bool
     */
    protected $_dateFilterVisibility = true;

    /**
     * Filters array
     *
     * @var array
     */
    protected $_filters = [];

    /**
     * Default filters values
     *
     * @var array
     */
    protected $_defaultFilters = ['report_from' => '', 'report_to' => '', 'report_period' => 'day'];

    /**
     * Sub-report rows count
     *
     * @var int
     */
    protected $_subReportSize = 5;

    /**
     * Errors messages aggregated array
     *
     * @var array
     */
    protected $_errors = [];

    /**
     * Block template file name
     *
     * @var string
     */
    protected $_template = 'Magento_Reports::grid.phtml';

    /**
     * Filter values array
     *
     * @var array
     */
    protected $_filterValues;

    /**
     * Apply sorting and filtering to collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $filter = $this->getParam($this->getVarNameFilter(), null);

        if (is_null($filter)) {
            $filter = $this->_defaultFilter;
        }

        if (is_string($filter)) {
            $data = [];
            $filter = base64_decode($filter);
            parse_str(urldecode($filter), $data);

            if (!isset($data['report_from'])) {
                // getting all reports from 2001 year
                $date = new \Magento\Framework\Stdlib\DateTime\Date(mktime(0, 0, 0, 1, 1, 2001));
                $data['report_from'] = $date->toString($this->_localeDate->getDateFormat('short'));
            }

            if (!isset($data['report_to'])) {
                // getting all reports from 2001 year
                $date = new \Magento\Framework\Stdlib\DateTime\Date();
                $data['report_to'] = $date->toString($this->_localeDate->getDateFormat('short'));
            }

            $this->_setFilterValues($data);
        } elseif ($filter && is_array($filter)) {
            $this->_setFilterValues($filter);
        } elseif (0 !== sizeof($this->_defaultFilter)) {
            $this->_setFilterValues($this->_defaultFilter);
        }

        /** @var $collection \Magento\Reports\Model\Resource\Report\Collection */
        $collection = $this->getCollection();
        if ($collection) {
            $collection->setPeriod($this->getFilter('report_period'));

            if ($this->getFilter('report_from') && $this->getFilter('report_to')) {
                /**
                 * Validate from and to date
                 */
                try {
                    $from = $this->_localeDate->date(
                        $this->getFilter('report_from'),
                        \Zend_Date::DATE_SHORT,
                        null,
                        false
                    );
                    $to = $this->_localeDate->date($this->getFilter('report_to'), \Zend_Date::DATE_SHORT, null, false);

                    $collection->setInterval($from, $to);
                } catch (\Exception $e) {
                    $this->_errors[] = __('Invalid date specified');
                }
            }

            $collection->setStoreIds($this->_getAllowedStoreIds());

            if ($this->getSubReportSize() !== null) {
                $collection->setPageSize($this->getSubReportSize());
            }

            $this->_eventManager->dispatch(
                'adminhtml_widget_grid_filter_collection',
                ['collection' => $this->getCollection(), 'filter_values' => $this->_filterValues]
            );
        }

        return $this;
    }

    /**
     * Get allowed stores
     *
     * @return array
     */
    protected function _getAllowedStoreIds()
    {
        /**
         * Getting and saving store ids for website & group
         */
        $storeIds = [];
        if ($this->getRequest()->getParam('store')) {
            $storeIds = [$this->getParam('store')];
        } elseif ($this->getRequest()->getParam('website')) {
            $storeIds = $this->_storeManager->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
        } elseif ($this->getRequest()->getParam('group')) {
            $storeIds = $storeIds = $this->_storeManager->getGroup(
                $this->getRequest()->getParam('group')
            )->getStoreIds();
        }

        // By default storeIds array contains only allowed stores
        $allowedStoreIds = array_keys($this->_storeManager->getStores());
        // And then array_intersect with post data for prevent unauthorized stores reports
        $storeIds = array_intersect($allowedStoreIds, $storeIds);
        // If selected all websites or unauthorized stores use only allowed
        if (empty($storeIds)) {
            $storeIds = $allowedStoreIds;
        }
        // reset array keys
        $storeIds = array_values($storeIds);

        return $storeIds;
    }

    /**
     * Set filter values
     *
     * @param array $data
     * @return $this
     */
    protected function _setFilterValues($data)
    {
        foreach ($data as $name => $value) {
            $this->setFilter($name, $data[$name]);
        }
        return $this;
    }

    /**
     * Set visibility of store switcher
     *
     * @param bool $visible
     *
     * @return void
     */
    public function setStoreSwitcherVisibility($visible = true)
    {
        $this->_storeSwitcherVisibility = $visible;
    }

    /**
     * Return visibility of store switcher
     *
     * @return bool
     */
    public function getStoreSwitcherVisibility()
    {
        return $this->_storeSwitcherVisibility;
    }

    /**
     * Return store switcher html
     *
     * @return string
     */
    public function getStoreSwitcherHtml()
    {
        return $this->getChildHtml('store_switcher');
    }

    /**
     * Set visibility of date filter
     *
     * @param bool $visible
     *
     * @return void
     */
    public function setDateFilterVisibility($visible = true)
    {
        $this->_dateFilterVisibility = $visible;
    }

    /**
     * Return visibility of date filter
     *
     * @return bool
     */
    public function getDateFilterVisibility()
    {
        return $this->_dateFilterVisibility;
    }

    /**
     * Return date filter html
     *
     * @return string
     */
    public function getDateFilterHtml()
    {
        return $this->getChildHtml('date_filter');
    }

    /**
     * Get periods
     *
     * @return mixed
     */
    public function getPeriods()
    {
        return $this->getCollection()->getPeriods();
    }

    /**
     * Get date format according the locale
     *
     * @return string
     */
    public function getDateFormat()
    {
        return $this->_localeDate->getDateFormat(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT);
    }

    /**
     * Return refresh button html
     *
     * @return string
     */
    public function getRefreshButtonHtml()
    {
        return $this->getChildHtml('refresh_button');
    }

    /**
     * Set filter
     *
     * @param string $name
     * @param string $value
     *
     * @return void
     */
    public function setFilter($name, $value)
    {
        if ($name) {
            $this->_filters[$name] = $value;
        }
    }

    /**
     * Get filter by key
     *
     * @param string $name
     * @return string
     */
    public function getFilter($name)
    {
        if (isset($this->_filters[$name])) {
            return $this->_filters[$name];
        } else {
            return $this->getRequest()->getParam($name) ? htmlspecialchars($this->getRequest()->getParam($name)) : '';
        }
    }

    /**
     * Set sub-report rows count
     *
     * @param int $size
     *
     * @return void
     */
    public function setSubReportSize($size)
    {
        $this->_subReportSize = $size;
    }

    /**
     * Return sub-report rows count
     *
     * @return int
     */
    public function getSubReportSize()
    {
        return $this->_subReportSize;
    }

    /**
     * Retrieve errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Prepare grid filter buttons
     *
     * @return void
     */
    protected function _prepareFilterButtons()
    {
        $this->addChild(
            'refresh_button',
            'Magento\Backend\Block\Widget\Button',
            ['label' => __('Refresh'), 'onclick' => "{$this->getJsObjectName()}.doFilter();", 'class' => 'task']
        );
    }
}
