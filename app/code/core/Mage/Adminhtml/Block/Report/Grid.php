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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml report grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Report_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_storeSwitcherVisibility = true;

    protected $_dateFilterVisibility = true;

    protected $_exportVisibility = true;

    protected $_subtotalVisibility = false;

    protected $_filters = array();

    /**
     * @var Mage_Core_Model_Locale
     */
    protected $_locale;

    protected $_defaultFilters = array(
            'report_from' => '',
            'report_to' => '',
            'report_period' => 'day'
        );

    protected $_subReportSize = 5;

    protected $_grandTotals;

    protected $_errors = array();

    /**
     * stores current currency code
     */
    protected $_currentCurrencyCode = null;

    public function __construct()
    {
        parent::__construct();
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setTemplate('Mage_Adminhtml::report/grid.phtml');
        $this->setUseAjax(false);
        $this->setCountTotals(true);
    }

    protected function _prepareLayout()
    {
        $this->setChild('store_switcher',
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_Store_Switcher')
                ->setUseConfirm(false)
                ->setSwitchUrl($this->getUrl('*/*/*', array('store'=>null)))
                ->setTemplate('report/store/switcher.phtml')
        );

        $this->addChild('refresh_button', 'Mage_Adminhtml_Block_Widget_Button', array(
            'label'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Refresh'),
            'onclick'   => $this->getRefreshButtonCallback(),
            'class'   => 'task'
        ));
        parent::_prepareLayout();
        return $this;
    }

    protected function _prepareColumns()
    {
        foreach ($this->_columns as $_column) {
            $_column->setSortable(false);
        }

        parent::_prepareColumns();
    }

    protected function _prepareCollection()
    {
        $filter = $this->getParam($this->getVarNameFilter(), null);

        if (is_null($filter)) {
            $filter = $this->_defaultFilter;
        }

        if (is_string($filter)) {
            $data = array();
            $filter = base64_decode($filter);
            parse_str(urldecode($filter), $data);

            if (!isset($data['report_from'])) {
                // getting all reports from 2001 year
                $date = new Zend_Date(mktime(0,0,0,1,1,2001));
                $data['report_from'] = $date->toString($this->getLocale()->getDateFormat('short'));
            }

            if (!isset($data['report_to'])) {
                // getting all reports from 2001 year
                $date = new Zend_Date();
                $data['report_to'] = $date->toString($this->getLocale()->getDateFormat('short'));
            }

            $this->_setFilterValues($data);
        } else if ($filter && is_array($filter)) {
            $this->_setFilterValues($filter);
        } else if(0 !== sizeof($this->_defaultFilter)) {
            $this->_setFilterValues($this->_defaultFilter);
        }
        /** @var $collection Mage_Reports_Model_Resource_Report_Collection */
        $collection = Mage::getResourceModel('Mage_Reports_Model_Resource_Report_Collection');

        $collection->setPeriod($this->getFilter('report_period'));

        if ($this->getFilter('report_from') && $this->getFilter('report_to')) {
            /**
             * Validate from and to date
             */
            try {
                $from = $this->getLocale()->date($this->getFilter('report_from'), Zend_Date::DATE_SHORT, null, false);
                $to   = $this->getLocale()->date($this->getFilter('report_to'), Zend_Date::DATE_SHORT, null, false);

                $collection->setInterval($from, $to);
            }
            catch (Exception $e) {
                $this->_errors[] = Mage::helper('Mage_Reports_Helper_Data')->__('Invalid date specified.');
            }
        }

        /**
         * Getting and saving store ids for website & group
         */
        $storeIds = array();
        if ($this->getRequest()->getParam('store')) {
            $storeIds = array($this->getParam('store'));
        } elseif ($this->getRequest()->getParam('website')){
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
        } elseif ($this->getRequest()->getParam('group')){
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
        }

        // By default storeIds array contains only allowed stores
        $allowedStoreIds = array_keys(Mage::app()->getStores());
        // And then array_intersect with post data for prevent unauthorized stores reports
        $storeIds = array_intersect($allowedStoreIds, $storeIds);
        // If selected all websites or unauthorized stores use only allowed
        if (empty($storeIds)) {
            $storeIds = $allowedStoreIds;
        }
        // reset array keys
        $storeIds = array_values($storeIds);


        $collection->setStoreIds($storeIds);

        if ($this->getSubReportSize() !== null) {
            $collection->setPageSize($this->getSubReportSize());
        }

        $this->setCollection($collection);

        Mage::dispatchEvent('adminhtml_widget_grid_filter_collection',
                array('collection' => $this->getCollection(), 'filter_values' => $this->_filterValues)
        );
    }

    protected function _setFilterValues($data)
    {
        foreach ($data as $name => $value) {
            //if (isset($data[$name])) {
                $this->setFilter($name, $data[$name]);
            //}
        }
        return $this;
    }

    /**
     * Set visibility of store switcher
     *
     * @param boolean $visible
     */
    public function setStoreSwitcherVisibility($visible=true)
    {
        $this->_storeSwitcherVisibility = $visible;
    }

    /**
     * Return visibility of store switcher
     *
     * @return boolean
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
     * @param boolean $visible
     */
    public function setDateFilterVisibility($visible=true)
    {
        $this->_dateFilterVisibility = $visible;
    }

    /**
     * Return visibility of date filter
     *
     * @return boolean
     */
    public function getDateFilterVisibility()
    {
        return $this->_dateFilterVisibility;
    }

    /**
     * Set visibility of export action
     *
     * @param boolean $visible
     */
    public function setExportVisibility($visible=true)
    {
        $this->_exportVisibility = $visible;
    }

    /**
     * Return visibility of export action
     *
     * @return boolean
     */
    public function getExportVisibility()
    {
        return $this->_exportVisibility;
    }

    /**
     * Set visibility of subtotals
     *
     * @param boolean $visible
     */
    public function setSubtotalVisibility($visible=true)
    {
        $this->_subtotalVisibility = $visible;
    }

    /**
     * Return visibility of subtotals
     *
     * @return boolean
     */
    public function getSubtotalVisibility()
    {
        return $this->_subtotalVisibility;
    }

    public function getPeriods()
    {
        return $this->getCollection()->getPeriods();
    }

    public function getDateFormat()
    {
        return $this->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
    }

    /**
     * Return refresh button html
     */
    public function getRefreshButtonHtml()
    {
        return $this->getChildHtml('refresh_button');
    }

    public function setFilter($name, $value)
    {
        if ($name) {
            $this->_filters[$name] = $value;
        }
    }

    public function getFilter($name)
    {
        if (isset($this->_filters[$name])) {
            return $this->_filters[$name];
        } else {
            return ($this->getRequest()->getParam($name))
                    ?htmlspecialchars($this->getRequest()->getParam($name)):'';
        }
    }

    public function setSubReportSize($size)
    {
        $this->_subReportSize = $size;
    }

    public function getSubReportSize()
    {
        return $this->_subReportSize;
    }

    /**
     * Retrieve locale
     *
     * @return Mage_Core_Model_Locale
     */
    public function getLocale()
    {
        if (!$this->_locale) {
            $this->_locale = Mage::app()->getLocale();
        }
        return $this->_locale;
    }

    /**
     * Add new export type to grid
     *
     * @param   string $url
     * @param   string $label
     * @return  Mage_Adminhtml_Block_Widget_Grid
     */
    public function addExportType($url, $label)
    {
        $this->_exportTypes[] = new Varien_Object(
            array(
                'url'   => $this->getUrl($url,
                    array(
                        '_current'=>true,
                        'filter' => $this->getParam($this->getVarNameFilter(), null)
                        )
                    ),
                'label' => $label
            )
        );
        return $this;
    }

    public function getReport($from, $to)
    {
        if ($from == '') {
            $from = $this->getFilter('report_from');
        }
        if ($to == '') {
            $to = $this->getFilter('report_to');
        }
        $totalObj = Mage::getModel('Mage_Reports_Model_Totals');
        $this->setTotals($totalObj->countTotals($this, $from, $to));
        $this->addGrandTotals($this->getTotals());
        return $this->getCollection()->getReport($from, $to);
    }

    public function addGrandTotals($total)
    {
        $totalData = $total->getData();
        foreach ($totalData as $key=>$value) {
            $_column = $this->getColumn($key);
            if ($_column->getTotal() != '') {
                $this->getGrandTotals()->setData($key, $this->getGrandTotals()->getData($key)+$value);
            }
        }
        /*
         * recalc totals if we have average
         */
        foreach ($this->getColumns() as $key=>$_column) {
            if (strpos($_column->getTotal(), '/') !== FALSE) {
                list($t1, $t2) = explode('/', $_column->getTotal());
                if ($this->getGrandTotals()->getData($t2) != 0) {
                    $this->getGrandTotals()->setData(
                        $key,
                        (float)$this->getGrandTotals()->getData($t1)/$this->getGrandTotals()->getData($t2)
                    );
                }
            }
        }
    }

    public function getGrandTotals()
    {
        if (!$this->_grandTotals) {
            $this->_grandTotals = new Varien_Object();
        }
        return $this->_grandTotals;
    }

    public function getPeriodText()
    {
        return $this->__('Period');
    }

    /**
     * Retrieve grid as CSV
     *
     * @return unknown
     */
    public function getCsv()
    {
        $csv = '';
        $this->_prepareGrid();

        $data = array('"'.$this->__('Period').'"');
        foreach ($this->_columns as $column) {
            if (!$column->getIsSystem()) {
                $data[] = '"'.$column->getHeader().'"';
            }
        }
        $csv.= implode(',', $data)."\n";

        foreach ($this->getCollection()->getIntervals() as $_index=>$_item) {
            $report = $this->getReport($_item['start'], $_item['end']);
            foreach ($report as $_subIndex=>$_subItem) {
                $data = array('"'.$_index.'"');
                foreach ($this->_columns as $column) {
                    if (!$column->getIsSystem()) {
                        $data[] = '"' . str_replace(
                            array('"', '\\'),
                            array('""', '\\\\'),
                            $column->getRowField($_subItem)
                        ) . '"';
                    }
                }
                $csv.= implode(',', $data)."\n";
            }
            if ($this->getCountTotals() && $this->getSubtotalVisibility())
            {
                $data = array('"'.$_index.'"');
                $j = 0;
                foreach ($this->_columns as $column) {
                    $j++;
                    if (!$column->getIsSystem()) {
                        $data[] = ($j == 1) ?
                                '"' . $this->__('Subtotal') . '"' :
                                '"'.str_replace('"', '""', $column->getRowField($this->getTotals())).'"';
                    }
                }
                $csv.= implode(',', $data)."\n";
            }
        }

        if ($this->getCountTotals())
        {
            $data = array('"'.$this->__('Total').'"');
            foreach ($this->_columns as $column) {
                if (!$column->getIsSystem()) {
                    $data[] = '"'.str_replace('"', '""', $column->getRowField($this->getGrandTotals())).'"';
                }
            }
            $csv.= implode(',', $data)."\n";
        }

        return $csv;
    }

    /**
     * Retrieve grid as Excel Xml
     *
     * @return unknown
     */
    public function getExcel($filename = '')
    {
        $this->_prepareGrid();

        $data = array();
        $row = array($this->__('Period'));
        foreach ($this->_columns as $column) {
            if (!$column->getIsSystem()) {
                $row[] = $column->getHeader();
            }
        }
        $data[] = $row;

        foreach ($this->getCollection()->getIntervals() as $_index=>$_item) {
            $report = $this->getReport($_item['start'], $_item['end']);
            foreach ($report as $_subIndex=>$_subItem) {
                $row = array($_index);
                foreach ($this->_columns as $column) {
                    if (!$column->getIsSystem()) {
                        $row[] = $column->getRowField($_subItem);
                    }
                }
                $data[] = $row;
            }
            if ($this->getCountTotals() && $this->getSubtotalVisibility())
            {
                $row = array($_index);
                $j = 0;
                foreach ($this->_columns as $column) {
                    $j++;
                    if (!$column->getIsSystem()) {
                        $row[] = ($j==1)?$this->__('Subtotal'):$column->getRowField($this->getTotals());
                    }
                }
                $data[] = $row;
            }
        }

        if ($this->getCountTotals())
        {
            $row = array($this->__('Total'));
            foreach ($this->_columns as $column) {
                if (!$column->getIsSystem()) {
                    $row[] = $column->getRowField($this->getGrandTotals());
                }
            }
            $data[] = $row;
        }

        $convert = new Magento_Convert_Excel(new ArrayIterator($data));
        return $convert->convert('single_sheet');
    }

    public function getSubtotalText()
    {
        return $this->__('Subtotal');
    }

    public function getTotalText()
    {
        return $this->__('Total');
    }

    public function getEmptyText()
    {
        return $this->__('No records found for this period.');
    }

    public function getCountTotals()
    {
        $totals = $this->getGrandTotals()->getData();
        if (parent::getCountTotals() && count($totals)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * onlick event for refresh button to show alert if fields are empty
     *
     * @return string
     */
    public function getRefreshButtonCallback()
    {
        return "{$this->getJsObjectName()}.doFilter();";
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
     * Retrieve correct currency code for select website, store, group
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        if (is_null($this->_currentCurrencyCode)) {
            if ($this->getRequest()->getParam('store')) {
                $store = $this->getRequest()->getParam('store');
                $this->_currentCurrencyCode = Mage::app()->getStore($store)->getBaseCurrencyCode();
            } else if ($this->getRequest()->getParam('website')){
                $website = $this->getRequest()->getParam('website');
                $this->_currentCurrencyCode = Mage::app()->getWebsite($website)->getBaseCurrencyCode();
            } else if ($this->getRequest()->getParam('group')){
                $group = $this->getRequest()->getParam('group');
                $this->_currentCurrencyCode =  Mage::app()->getGroup($group)->getWebsite()->getBaseCurrencyCode();
            } else {
                $this->_currentCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
            }
        }
        return $this->_currentCurrencyCode;
    }
    
    /**
     * Get currency rate (base to given currency)
     *
     * @param string|Mage_Directory_Model_Currency $currencyCode
     * @return double
     */
    public function getRate($toCurrency)
    {
        return Mage::app()->getStore()->getBaseCurrency()->getRate($toCurrency);
    }
}
