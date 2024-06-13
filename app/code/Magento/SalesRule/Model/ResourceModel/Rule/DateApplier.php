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
     * @param \Magento\Framework\DB\Select $select
     * @param int|string $now
     * @return void
     */
    public function applyDate($select, $now)
    {
        $select->where(
            'from_date is null or from_date <= ?',
            $now
        )->where(
            'to_date is null or to_date >= ?',
            $now
        );
    }
}
