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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Report Reviews collection
 *
 * @category    Mage
 * @package     Mage_Reports
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Reports_Model_Resource_Report_Collection
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
     * Model object
     *
     * @var string
     */
    protected $_model;

    /**
     * Intervals
     *
     * @var int
     */
    protected $_intervals;

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
     * Resource initialization
     *
     */
    protected function _construct()
    {

    }

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
     * @param int $from
     * @param int $to
     * @return Mage_Reports_Model_Resource_Report_Collection
     */
    public function setInterval($from, $to)
    {
        $this->_from = $from;
        $this->_to   = $to;

        return $this;
    }

    /**
     * Get intervals
     *
     * @return unknown
     */
    public function getIntervals()
    {
        if (!$this->_intervals) {
            $this->_intervals = array();
            if (!$this->_from && !$this->_to) {
                return $this->_intervals;
            }
            $dateStart  = new Zend_Date($this->_from);
            $dateEnd    = new Zend_Date($this->_to);


            $t = array();
            $firstInterval = true;
            while ($dateStart->compare($dateEnd) <= 0) {

                switch ($this->_period) {
                    case 'day':
                        $t['title'] = $dateStart->toString(Mage::app()->getLocale()->getDateFormat());
                        $t['start'] = $dateStart->toString('yyyy-MM-dd HH:mm:ss');
                        $t['end'] = $dateStart->toString('yyyy-MM-dd 23:59:59');
                        $dateStart->addDay(1);
                        break;
                    case 'month':
                        $t['title'] =  $dateStart->toString('MM/yyyy');
                        $t['start'] = ($firstInterval) ? $dateStart->toString('yyyy-MM-dd 00:00:00')
                            : $dateStart->toString('yyyy-MM-01 00:00:00');

                        $lastInterval = ($dateStart->compareMonth($dateEnd->getMonth()) == 0);

                        $t['end'] = ($lastInterval) ? $dateStart->setDay($dateEnd->getDay())
                            ->toString('yyyy-MM-dd 23:59:59')
                            : $dateStart->toString('yyyy-MM-'.date('t', $dateStart->getTimestamp()).' 23:59:59');

                        $dateStart->addMonth(1);

                        if ($dateStart->compareMonth($dateEnd->getMonth()) == 0) {
                            $dateStart->setDay(1);
                        }

                        $firstInterval = false;
                        break;
                    case 'year':
                        $t['title'] =  $dateStart->toString('yyyy');
                        $t['start'] = ($firstInterval) ? $dateStart->toString('yyyy-MM-dd 00:00:00')
                            : $dateStart->toString('yyyy-01-01 00:00:00');

                        $lastInterval = ($dateStart->compareYear($dateEnd->getYear()) == 0);

                        $t['end'] = ($lastInterval) ? $dateStart->setMonth($dateEnd->getMonth())
                            ->setDay($dateEnd->getDay())->toString('yyyy-MM-dd 23:59:59')
                            : $dateStart->toString('yyyy-12-31 23:59:59');
                        $dateStart->addYear(1);

                        if ($dateStart->compareYear($dateEnd->getYear()) == 0) {
                            $dateStart->setMonth(1)->setDay(1);
                        }

                        $firstInterval = false;
                        break;
                }
                $this->_intervals[$t['title']] = $t;
            }
        }
        return  $this->_intervals;
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
        return count($this->getIntervals());
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
     * Init report
     *
     * @param string $modelClass
     * @return Mage_Reports_Model_Resource_Report_Collection
     */
    public function initReport($modelClass)
    {
        $this->_model = Mage::getModel('Mage_Reports_Model_Report')
            ->setPageSize($this->getPageSize())
            ->setStoreIds($this->getStoreIds())
            ->initCollection($modelClass);

        return $this;
    }

    /**
     * get report full
     *
     * @param int $from
     * @param int $to
     * @return unknown
     */
    public function getReportFull($from, $to)
    {
        return $this->_model->getReportFull($this->timeShift($from), $this->timeShift($to));
    }

    /**
     * Get report
     *
     * @param int $from
     * @param int $to
     * @return Varien_Object
     */
    public function getReport($from, $to)
    {
        return $this->_model->getReport($this->timeShift($from), $this->timeShift($to));
    }

    /**
     * Retreive time shift
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
}
