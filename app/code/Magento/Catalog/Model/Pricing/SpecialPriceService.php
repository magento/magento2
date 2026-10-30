<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Pricing;

/**
 * This class provides functionality to normalize the end date/time of special prices
 */
class SpecialPriceService
{
    /**
     * This class subtracts one day from $dateTo if it contains a specific time (hours, minutes, seconds)
     * because \Magento\Framework\Stdlib\DateTime\Timezone::isScopeDateInInterval adds one day.
     * This ensures that the special price expires exactly at the specified time
     *
     * For example,
     * - If $dateTo is "2025-05-12 17:00:00", it will be converted to "2025-05-11 17:00:00"
     * - If $dateTo is "2024-05-12 00:00:00", it will remain unchanged
     *
     * @param mixed $dateTo
     * @return mixed|string
     */
    public function execute(mixed $dateTo): mixed
    {
        if ($dateTo
            && strtotime($dateTo) !== false
            && date('H:i:s', strtotime($dateTo)) !== '00:00:00') {
            $dateToTimestamp = strtotime($dateTo);
            $dateTo = date('Y-m-d H:i:s', $dateToTimestamp - 86400);
        }

        return $dateTo;
    }
}
