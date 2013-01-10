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
 * @package     Mage_Reports
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Report Reviews collection
 *
 * @category    Mage
 * @package     Mage_Reports
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Reports_Model_Resource_Report_Collection extends Varien_Data_Collection
{
    /**
     * From value
     *
     * @var string
     */
    protected $_from;

    /**
     * To value
     *
     * @var string
     */
    protected $_to;

    /**
     * Report period
     *
     * @var int
     */
    protected $_period;

    /**
     * Intervals
     *
     * @var int
     */
    protected $_intervals;

    /**
     * Intervals
     *
     * @var int
     */
    protected $_reports;

    /**
     * Page size
     *
     * @var int
     */
    protected $_pageSize;

    /**
     * Array of store ids
     *
     * @var array
     */
    protected $_storeIds;

    /**
     * Set the resource report collection class
     *
     * @var string
     */
    protected $_reportCollection = null;

    /**
     * Set period
     *
     * @param int $period
     * @return Mage_Reports_Model_Resource_Report_Collection
     */
    public function setPeriod($period)
    {
        $this->_period = $period;
        return $this;
    }

    /**
     * Set interval
     *
     * @param int $fromDate
     * @param int $toDate
     * @return Mage_Reports_Model_Resource_Report_Collection
     */
    public function setInterval($fromDate, $toDate)
    {
        $this->_from = $fromDate;
        $this->_to   = $toDate;

        return $this;
    }

    /**
     * Get intervals
     *
     * @return array
     */
    protected function _getIntervals()
    {
        if (!$this->_intervals) {
            $this->_intervals = array();
            if (!$this->_from && !$this->_to) {
                return $this->_intervals;
            }
            $dateStart  = new Zend_Date($this->_from);
            $dateEnd    = new Zend_Date($this->_to);

            $interval = array();
            $firstInterval = true;
            while ($dateStart->compare($dateEnd) <= 0) {
                switch ($this->_period) {
                    case 'day':
                        $interval = $this->_getDayInterval($dateStart);
                        $dateStart->addDay(1);
                        break;
                    case 'month':
                        $interval = $this->_getMonthInterval($dateStart, $dateEnd, $firstInterval);
                        $firstInterval = false;
                        break;
                    case 'year':
                        $interval = $this->_getYearInterval($dateStart, $dateEnd, $firstInterval);
                        $firstInterval = false;
                        break;
                }
                $this->_intervals[$interval['period']] = new Varien_Object($interval);
            }
        }
        return  $this->_intervals;
    }

    /**
     * Get interval for a day
     *
     * @param Zend_Date $dateStart
     * @return array
     */
    protected function _getDayInterval(Zend_Date $dateStart)
    {
        $interval = array(
                'period' => $dateStart->toString(Mage::app()->getLocale()->getDateFormat()),
                'start'  => $dateStart->toString('yyyy-MM-dd HH:mm:ss'),
                'end'    => $dateStart->toString('yyyy-MM-dd 23:59:59')
        );
        return $interval;
    }

    /**
     * Get interval for a month
     *
     * @param Zend_Date $dateStart
     * @param Zend_Date $dateEnd
     * @param bool $firstInterval
     * @return array
     */
    protected function _getMonthInterval(Zend_Date $dateStart, Zend_Date $dateEnd, $firstInterval)
    {
        $interval = array();
        $interval['period'] =  $dateStart->toString('MM/yyyy');
        $interval['start'] = ($firstInterval) ? $dateStart->toString('yyyy-MM-dd 00:00:00')
            : $dateStart->toString('yyyy-MM-01 00:00:00');

        $lastInterval = ($dateStart->compareMonth($dateEnd->getMonth()) == 0);

        $interval['end'] = ($lastInterval) ? $dateStart->setDay($dateEnd->getDay())
            ->toString('yyyy-MM-dd 23:59:59')
            : $dateStart->toString('yyyy-MM-'.date('t', $dateStart->getTimestamp()).' 23:59:59');

        $dateStart->addMonth(1);

        if ($dateStart->compareMonth($dateEnd->getMonth()) == 0) {
            $dateStart->setDay(1);
        }

        return $interval;
    }

    /**
     * Get Interval for a year
     *
     * @param Zend_Date $dateStart
     * @param Zend_Date $dateEnd
     * @param bool $firstInterval
     * @return array
     */
    protected function _getYearInterval(Zend_Date $dateStart, Zend_Date $dateEnd, $firstInterval)
    {
        $interval = array();
        $interval['period'] =  $dateStart->toString('yyyy');
        $interval['start'] = ($firstInterval) ? $dateStart->toString('yyyy-MM-dd 00:00:00')
            : $dateStart->toString('yyyy-01-01 00:00:00');

        $lastInterval = ($dateStart->compareYear($dateEnd->getYear()) == 0);

        $interval['end'] = ($lastInterval) ? $dateStart->setMonth($dateEnd->getMonth())
            ->setDay($dateEnd->getDay())->toString('yyyy-MM-dd 23:59:59')
            : $dateStart->toString('yyyy-12-31 23:59:59');
        $dateStart->addYear(1);

        if ($dateStart->compareYear($dateEnd->getYear()) == 0) {
            $dateStart->setMonth(1)->setDay(1);
        }

        return $interval;
    }

    /**
     * Return date periods
     *
     * @return array
     */
    public function getPeriods()
    {
        return array(
            'day'   => Mage::helper('Mage_Reports_Helper_Data')->__('Day'),
            'month' => Mage::helper('Mage_Reports_Helper_Data')->__('Month'),
            'year'  => Mage::helper('Mage_Reports_Helper_Data')->__('Year')
        );
    }

    /**
     * Set store ids
     *
     * @param array $storeIds
     * @return Mage_Reports_Model_Resource_Report_Collection
     */
    public function setStoreIds($storeIds)
    {
        $this->_storeIds = $storeIds;
        return $this;
    }

    /**
     * Get store ids
     *
     * @return arrays
     */
    public function getStoreIds()
    {
        return $this->_storeIds;
    }

    /**
     * Get size
     *
     * @return int
     */
    public function getSize()
    {
        return count($this->_getIntervals());
    }

    /**
     * Set page size
     *
     * @param int $size
     * @return Mage_Reports_Model_Resource_Report_Collection
     */
    public function setPageSize($size)
    {
        $this->_pageSize = $size;
        return $this;
    }

    /**
     * Get page size
     *
     * @return int
     */
    public function getPageSize()
    {
        return $this->_pageSize;
    }

    /**
     * get report full
     *
     * @param int $fromDate
     * @param int $toDate
     * @return unknown
     */
    public function getReportFull($fromDate, $toDate)
    {
        return $this->_model->getReportFull($this->timeShift($fromDate), $this->timeShift($toDate));
    }

    /**
     * Get report for some interval
     *
     * @param int $fromDate
     * @param int $toDate
     * @return Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    protected function _getReport($fromDate, $toDate)
    {
        if ($this->_reportCollection === null) {
            return array();
        }
        $collectionClass = $this->_reportCollection;
        $reportResource = new $collectionClass();
        $reportResource
            ->setDateRange($this->timeShift($fromDate), $this->timeShift($toDate))
            ->setStoreIds($this->getStoreIds());
        return $reportResource;

    }

    /**
     * Get Reports based on intervals
     *
     * @return array
     */
    public function getReports()
    {
        if (!$this->_reports) {
            $reports = array();
            foreach ($this->_getIntervals() as $interval) {
                $interval->setChildren(
                    $this->_getReport($interval->getStart(), $interval->getEnd())
                );
                if (count($interval->getChildren()) == 0) {
                    $interval->setIsEmpty(true);
                }
                $reports[] = $interval;
            }
            $this->_reports = $reports;
        }
        return $this->_reports;
    }

    /**
     * Retrieve time shift
     *
     * @param string $datetime
     * @return string
     */
    public function timeShift($datetime)
    {
        return Mage::app()->getLocale()
            ->utcDate(null, $datetime, true, Varien_Date::DATETIME_INTERNAL_FORMAT)
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
    }

    /**
     * Load data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return Mage_Reports_Model_Resource_Report_Collection|Varien_Data_Collection
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        $this->_items = $this->getReports();
        return $this;
    }
}
