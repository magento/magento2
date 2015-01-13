<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Report Reviews collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\Resource\Report;

class Collection extends \Magento\Framework\Data\Collection
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
     * @var  \Magento\Reports\Model\DateFactory
     */
    protected $_dateFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Reports\Model\Resource\Report\Collection\Factory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Reports\Model\DateFactory $dateFactory
     * @param \Magento\Reports\Model\Resource\Report\Collection\Factory $collectionFactory
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Reports\Model\DateFactory $dateFactory,
        \Magento\Reports\Model\Resource\Report\Collection\Factory $collectionFactory
    ) {
        $this->_dateFactory = $dateFactory;
        $this->_localeDate = $localeDate;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($entityFactory);
    }

    /**
     * Set period
     *
     * @param int $period
     * @return $this
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
     * @return $this
     */
    public function setInterval($fromDate, $toDate)
    {
        $this->_from = $fromDate;
        $this->_to = $toDate;

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
            $this->_intervals = [];
            if (!$this->_from && !$this->_to) {
                return $this->_intervals;
            }
            $dateStart = $this->_dateFactory->create($this->_from);
            $dateEnd = $this->_dateFactory->create($this->_to);

            $interval = [];
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
                    default:
                        break 2;
                }
                $this->_intervals[$interval['period']] = new \Magento\Framework\Object($interval);
            }
        }
        return $this->_intervals;
    }

    /**
     * Get interval for a day
     *
     * @param \Magento\Framework\Stdlib\DateTime\DateInterface $dateStart
     * @return array
     */
    protected function _getDayInterval(\Magento\Framework\Stdlib\DateTime\DateInterface $dateStart)
    {
        $interval = [
            'period' => $dateStart->toString($this->_localeDate->getDateFormat()),
            'start' => $dateStart->toString('yyyy-MM-dd HH:mm:ss'),
            'end' => $dateStart->toString('yyyy-MM-dd 23:59:59'),
        ];
        return $interval;
    }

    /**
     * Get interval for a month
     *
     * @param \Magento\Framework\Stdlib\DateTime\DateInterface $dateStart
     * @param \Magento\Framework\Stdlib\DateTime\DateInterface $dateEnd
     * @param bool $firstInterval
     * @return array
     */
    protected function _getMonthInterval(
        \Magento\Framework\Stdlib\DateTime\DateInterface $dateStart,
        \Magento\Framework\Stdlib\DateTime\DateInterface $dateEnd,
        $firstInterval
    ) {
        $interval = [];
        $interval['period'] = $dateStart->toString('MM/yyyy');
        if ($firstInterval) {
            $interval['start'] = $dateStart->toString('yyyy-MM-dd 00:00:00');
        } else {
            $interval['start'] = $dateStart->toString('yyyy-MM-01 00:00:00');
        }

        $lastInterval = $dateStart->compareMonth($dateEnd->getMonth()) == 0;

        if ($lastInterval) {
            $interval['end'] = $dateStart->setDay($dateEnd->getDay())->toString('yyyy-MM-dd 23:59:59');
        } else {
            $interval['end'] = $dateStart->toString('yyyy-MM-' . date('t', $dateStart->getTimestamp()) . ' 23:59:59');
        }

        $dateStart->addMonth(1);

        if ($dateStart->compareMonth($dateEnd->getMonth()) == 0) {
            $dateStart->setDay(1);
        }

        return $interval;
    }

    /**
     * Get Interval for a year
     *
     * @param \Magento\Framework\Stdlib\DateTime\DateInterface $dateStart
     * @param \Magento\Framework\Stdlib\DateTime\DateInterface $dateEnd
     * @param bool $firstInterval
     * @return array
     */
    protected function _getYearInterval(
        \Magento\Framework\Stdlib\DateTime\DateInterface $dateStart,
        \Magento\Framework\Stdlib\DateTime\DateInterface $dateEnd,
        $firstInterval
    ) {
        $interval = [];
        $interval['period'] = $dateStart->toString('yyyy');
        $interval['start'] = $firstInterval ? $dateStart->toString(
            'yyyy-MM-dd 00:00:00'
        ) : $dateStart->toString(
            'yyyy-01-01 00:00:00'
        );

        $lastInterval = $dateStart->compareYear($dateEnd->getYear()) == 0;

        $interval['end'] = $lastInterval ? $dateStart->setMonth(
            $dateEnd->getMonth()
        )->setDay(
            $dateEnd->getDay()
        )->toString(
            'yyyy-MM-dd 23:59:59'
        ) : $dateStart->toString(
            'yyyy-12-31 23:59:59'
        );
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
        return ['day' => __('Day'), 'month' => __('Month'), 'year' => __('Year')];
    }

    /**
     * Set store ids
     *
     * @param array $storeIds
     * @return $this
     */
    public function setStoreIds($storeIds)
    {
        $this->_storeIds = $storeIds;
        return $this;
    }

    /**
     * Get store ids
     *
     * @return array
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
     * @return $this
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
     * Get report for some interval
     *
     * @param int $fromDate
     * @param int $toDate
     * @return \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
     */
    protected function _getReport($fromDate, $toDate)
    {
        if ($this->_reportCollection === null) {
            return [];
        }
        $reportResource = $this->_collectionFactory->create($this->_reportCollection);
        $reportResource->setDateRange(
            $this->timeShift($fromDate),
            $this->timeShift($toDate)
        )->setStoreIds(
            $this->getStoreIds()
        );
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
            $reports = [];
            foreach ($this->_getIntervals() as $interval) {
                $interval->setChildren($this->_getReport($interval->getStart(), $interval->getEnd()));
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
        return $this->_localeDate->utcDate(
            null,
            $datetime,
            true,
            \Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT
        )->toString(
            \Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT
        );
    }

    /**
     * Load data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        $this->_items = $this->getReports();
        return $this;
    }
}
