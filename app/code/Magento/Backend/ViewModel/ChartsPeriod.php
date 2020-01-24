<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\ViewModel;

use Magento\Backend\Helper\Dashboard\Data;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * View model for dashboard charts period select
 */
class ChartsPeriod implements ArgumentInterface
{
    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @param Data $dataHelper
     */
    public function __construct(Data $dataHelper)
    {
        $this->dataHelper = $dataHelper;
    }

    /**
     * Get chart date periods
     *
     * @return array
     */
    public function getDatePeriods(): array
    {
        return $this->dataHelper->getDatePeriods();
    }
}
