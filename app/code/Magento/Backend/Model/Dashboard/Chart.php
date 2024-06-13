<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Model\Dashboard;

use Magento\Backend\Helper\Dashboard\Order as OrderHelper;
use Magento\Backend\Model\Dashboard\Chart\Date;

/**
 * Dashboard chart data retriever
 */
class Chart
{
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
     */
    public function __construct(
        Date $dateRetriever,
        OrderHelper $orderHelper,
        Period $period
    ) {
        $this->dateRetriever = $dateRetriever;
        $this->orderHelper = $orderHelper;
        $this->period = $period;
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
        ?string $store = null,
        ?string $website = null,
        ?string $group = null
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
                $item = $collection->getItemByColumnValue('range', $date);

                $data[] = [
                    'x' => $date,
                    'y' => $item ? (float)$item->getData($chartParam) : 0
                ];
            }
        }

        return $data;
    }
}
