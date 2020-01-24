<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Model\Dashboard\Chart;

use DateTimeZone;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Reports\Model\ResourceModel\Order\CollectionFactory;

/**
 * Dashboard chart dates retriever
 */
class Date
{
    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * Date constructor.
     * @param CollectionFactory $collectionFactory
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        TimezoneInterface $localeDate
    ) {
        $this->localeDate = $localeDate;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get chart dates data by period
     *
     * @param string $period
     *
     * @return array
     */
    public function getByPeriod($period): array
    {
        [$dateStart, $dateEnd] = $this->collectionFactory->create()->getDateRange(
            $period,
            '',
            '',
            true
        );

        $timezoneLocal = $this->localeDate->getConfigTimezone();

        $dateStart->setTimezone(new DateTimeZone($timezoneLocal));
        $dateEnd->setTimezone(new DateTimeZone($timezoneLocal));

        if ($period === '24h') {
            $dateEnd->modify('-1 hour');
        } else {
            $dateEnd->setTime(23, 59, 59);
            $dateStart->setTime(0, 0, 0);
        }

        $dates = [];

        while ($dateStart <= $dateEnd) {
            switch ($period) {
                case '7d':
                case '1m':
                    $d = $dateStart->format('Y-m-d');
                    $dateStart->modify('+1 day');
                    break;
                case '1y':
                case '2y':
                    $d = $dateStart->format('Y-m');
                    $dateStart->modify('first day of next month');
                    break;
                default:
                    $d = $dateStart->format('Y-m-d H:00');
                    $dateStart->modify('+1 hour');
            }

            $dates[] = $d;
        }

        return $dates;
    }
}
