<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Report Reviews collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\ResourceModel\Report;

/**
 * @api
 */
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
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Reports\Model\ResourceModel\Report\Collection\Factory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Reports\Model\ResourceModel\Report\Collection\Factory $collectionFactory
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Reports\Model\ResourceModel\Report\Collection\Factory $collectionFactory
    ) {
        $this->_localeDate = $localeDate;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($entityFactory);
    }

    /**
     * Set period
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     *
     * @param \DateTimeInterface $fromDate
     * @param \DateTimeInterface $toDate
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
            $dateStart = $this->_from;
            $dateEnd = $this->_to;

            $firstInterval = true;
            while ($dateStart <= $dateEnd) {
                switch ($this->_period) {
                    case 'day':
                        $interval = $this->_getDayInterval($dateStart);
                        $dateStart->modify('+1 day');
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
                $this->_intervals[$interval['period']] = new \Magento\Framework\DataObject($interval);
            }
        }
        return $this->_intervals;
    }

    /**
     * Get interval for a day
     *
     * @param \DateTimeInterface $dateStart
     * @return array
     */
    protected function _getDayInterval(\DateTimeInterface $dateStart)
    {
        $interval = [
            'period' => $this->_localeDate->formatDateTime(
                $dateStart,
                \IntlDateFormatter::SHORT,
                \IntlDateFormatter::NONE
            ),
            'start' => $this->_localeDate->convertConfigTimeToUtc($dateStart->format('Y-m-d 00:00:00')),
            'end' => $this->_localeDate->convertConfigTimeToUtc($dateStart->format('Y-m-d 23:59:59')),
        ];
        return $interval;
    }

    /**
     * Get interval for a month
     *
     * @param \DateTimeInterface $dateStart
     * @param \DateTimeInterface $dateEnd
     * @param bool $firstInterval
     * @return array
     */
    protected function _getMonthInterval(\DateTimeInterface $dateStart, \DateTimeInterface $dateEnd, $firstInterval)
    {
        $interval = [];
        $interval['period'] = $dateStart->format('m/Y');
        if ($firstInterval) {
            $interval['start'] = $this->_localeDate->convertConfigTimeToUtc($dateStart->format('Y-m-d 00:00:00'));
        } else {
            $interval['start'] = $this->_localeDate->convertConfigTimeToUtc($dateStart->format('Y-m-01 00:00:00'));
        }

        if ($dateStart->diff($dateEnd)->m == 0) {
            $interval['end'] = $this->_localeDate->convertConfigTimeToUtc(
                $dateStart->setDate(
                    $dateStart->format('Y'),
                    $dateStart->format('m'),
                    $dateEnd->format('d')
                )->format(
                    'Y-m-d 23:59:59'
                )
            );
        } else {
            $interval['end'] = $this->_localeDate->convertConfigTimeToUtc(
                $dateStart->format('Y-m-' . date('t', $dateStart->getTimestamp()) . ' 23:59:59')
            );
        }

        $dateStart->modify('+1 month');

        if ($dateStart->diff($dateEnd)->m == 0) {
            $dateStart->setDate($dateStart->format('Y'), $dateStart->format('m'), 1);
        }

        return $interval;
    }

    /**
     * Get Interval for a year
     *
     * @param \DateTimeInterface $dateStart
     * @param \DateTimeInterface $dateEnd
     * @param bool $firstInterval
     * @return array
     */
    protected function _getYearInterval(\DateTimeInterface $dateStart, \DateTimeInterface $dateEnd, $firstInterval)
    {
        $interval = [];
        $interval['period'] = $dateStart->format('Y');
        $interval['start'] = $firstInterval
            ? $this->_localeDate->convertConfigTimeToUtc($dateStart->format('Y-m-d 00:00:00'))
            : $this->_localeDate->convertConfigTimeToUtc($dateStart->format('Y-01-01 00:00:00'));

        $interval['end'] = $dateStart->diff($dateEnd)->y == 0
            ? $this->_localeDate->convertConfigTimeToUtc(
                $dateStart->setDate($dateStart->format('Y'), $dateEnd->format('m'), $dateEnd->format('d'))
                    ->format('Y-m-d 23:59:59')
            )
            : $this->_localeDate->convertConfigTimeToUtc($dateStart->format('Y-12-31 23:59:59'));
        $dateStart->modify('+1 year');

        if ($dateStart->diff($dateEnd)->y == 0) {
            $dateStart->setDate($dateStart->format('Y'), 1, 1);
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    protected function _getReport($fromDate, $toDate)
    {
        if ($this->_reportCollection === null) {
            return [];
        }
        $reportResource = $this->_collectionFactory->create($this->_reportCollection);
        $reportResource->setDateRange($fromDate, $toDate)->setStoreIds($this->getStoreIds());
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
