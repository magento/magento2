<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\ViewModel;

use Magento\Backend\Model\Dashboard\Period;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * View model for dashboard charts period select
 */
class ChartsPeriod implements ArgumentInterface
{
    /**
     * @var Period
     */
    private $period;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param Period $period
     * @param Json $serializer
     */
    public function __construct(
        Period $period,
        Json $serializer
    ) {
        $this->period = $period;
        $this->serializer = $serializer;
    }

    /**
     * Get chart date periods
     *
     * @return array
     */
    public function getDatePeriods(): array
    {
        return $this->period->getDatePeriods();
    }

    /**
     * Get json-encoded chart period units
     *
     * @return string
     */
    public function getPeriodUnits(): string
    {
        return $this->serializer->serialize($this->period->getPeriodChartUnits());
    }
}
