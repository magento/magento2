<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\SalesRule\Model\ResourceModel\Rule;

/**
 * Class DateApplier
 * adds the dates just for SalesRule
 */
class DateApplier
{
    /**
     * Apply from_date and to_date filters to the select.
     *
     * @param \Magento\Framework\DB\Select $select
     * @param int|string $now
     * @return void
     */
    public function applyDate($select, $now)
    {
        $select->where(
            'main_table.from_date is null or main_table.from_date <= ?',
            $now
        )->where(
            'main_table.to_date is null or main_table.to_date >= ?',
            $now
        );
    }
}
