<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Model\Dashboard\Chart;

use Magento\Backend\Model\Dashboard\Period;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Reports\Model\ResourceModel\Order\CollectionFactory;

/**
 * Dashboard chart dates retriever
 */
class Date
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * Date constructor.
     * @param CollectionFactory $collectionFactory
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        TimezoneInterface $localeDate
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->localeDate = $localeDate;
    }

    /**
     * Get chart dates data by period
     *
     * @param string $period
     *
     * @return array
     */
    public function getByPeriod(string $period): array
    {
        [$dateStart, $dateEnd] = $this->collectionFactory->create()->getDateRange(
            $period,
            '',
            '',
            true
        );

        $timezoneLocal = $this->localeDate->getConfigTimezone();
        $localStartDate = new \DateTime($dateStart->format('Y-m-d H:i:s'), new \DateTimeZone($timezoneLocal));
        $localEndDate = new \DateTime($dateEnd->format('Y-m-d H:i:s'), new \DateTimeZone($timezoneLocal));

        if ($period === Period::PERIOD_24_HOURS) {
            $localEndDate = new \DateTime('now', new \DateTimeZone($timezoneLocal));
            $localStartDate = clone $localEndDate;
            $localStartDate->modify('-1 day');
            $localStartDate->modify('+1 hour');
        } elseif ($period === Period::PERIOD_TODAY) {
            $localEndDate->modify('now');
        } else {
            $localEndDate->setTime(23, 59, 59);
            $localStartDate->setTime(0, 0, 0);
        }

        $dates = [];

        while ($localStartDate <= $localEndDate) {
            switch ($period) {
                case Period::PERIOD_7_DAYS:
                case Period::PERIOD_1_MONTH:
                    $d = $localStartDate->format('Y-m-d');
                    $localStartDate->modify('+1 day');
                    break;
                case Period::PERIOD_1_YEAR:
                case Period::PERIOD_2_YEARS:
                    $d = $localStartDate->format('Y-m');
                    $localStartDate->modify('first day of next month');
                    break;
                default:
                    $d = $localStartDate->format('Y-m-d H:00');
                    $localStartDate->modify('+1 hour');
            }

            $dates[] = $d;
        }

        return $dates;
    }
}
