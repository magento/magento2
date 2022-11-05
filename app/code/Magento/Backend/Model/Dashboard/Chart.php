<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Model\Dashboard;

use Magento\Backend\Helper\Dashboard\Order as OrderHelper;
use Magento\Backend\Model\Dashboard\Chart\Date;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Dashboard chart data retriever
 */
class Chart
{
    /**
     * @var TimezoneInterface
     */
    private $timeZone;

    /**
     * @var Date
     */
    private $dateRetriever;

    /**
     * @var OrderHelper
     */
    private $orderHelper;

    /**
     * @var Period
     */
    private $period;

    /**
     * Chart constructor.
     * @param Date $dateRetriever
     * @param OrderHelper $orderHelper
     * @param Period $period
     * @param TimezoneInterface|null $timeZone
     */
    public function __construct(
        Date $dateRetriever,
        OrderHelper $orderHelper,
        Period $period,
        TimezoneInterface $timeZone = null
    ) {
        $this->dateRetriever = $dateRetriever;
        $this->orderHelper = $orderHelper;
        $this->period = $period;
        $this->timeZone = $timeZone ?: ObjectManager::getInstance()->get(TimezoneInterface::class);
    }

    /**
     * Get chart data by period and chart type parameter, with possibility to pass scope parameters
     *
     * @param string $period
     * @param string $chartParam
     * @param string|null $store
     * @param string|null $website
     * @param string|null $group
     *
     * @return array
     */
    public function getByPeriod(
        string $period,
        string $chartParam,
        string $store = null,
        string $website = null,
        string $group = null
    ): array {
        $this->orderHelper->setParam('store', $store);
        $this->orderHelper->setParam('website', $website);
        $this->orderHelper->setParam('group', $group);

        $availablePeriods = array_keys($this->period->getDatePeriods());
        $this->orderHelper->setParam(
            'period',
            $period && in_array($period, $availablePeriods, false) ? $period : Period::PERIOD_24_HOURS
        );

        $dates = $this->dateRetriever->getByPeriod($period);
        $collection = $this->orderHelper->getCollection();

        $data = [];

        if ($collection->count() > 0) {
            foreach ($dates as $date) {
                $utcDate = $this->getUTCDatetimeByPeriod($period, $date);
                $item = $collection->getItemByColumnValue('range', $utcDate);

                $data[] = [
                    'x' => $date,
                    'y' => $item ? (float)$item->getData($chartParam) : 0
                ];
            }
        }

        return $data;
    }

    /**
     * Get UTC date and time by period.
     *
     * @param string $period
     * @param string $date
     * @return string
     */
    private function getUTCDatetimeByPeriod(string $period, string $date)
    {
        switch ($period) {
            case Period::PERIOD_7_DAYS:
            case Period::PERIOD_1_MONTH:
                $utcDate = $this->timeZone->convertConfigTimeToUtc($date, 'Y-m-d');
                break;
            case Period::PERIOD_1_YEAR:
            case Period::PERIOD_2_YEARS:
                $utcDate = $this->timeZone->convertConfigTimeToUtc($date, 'Y-m');
                break;
            default:
                $utcDate = $this->timeZone->convertConfigTimeToUtc($date, 'Y-m-d H:00');
        }
        return $utcDate;
    }
}
