<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Widget\Grid;

/**
 * @api
 * @since 2.0.0
 */
class SubTotals extends \Magento\Backend\Model\Widget\Grid\AbstractTotals
{
    /**
     * Count collection column sum based on column index
     *
     * @param string $index
     * @param \Magento\Framework\Data\Collection $collection
     * @return float|int
     * @since 2.0.0
     */
    protected function _countSum($index, $collection)
    {
        $sum = 0;
        foreach ($collection as $item) {
            $sum += $item[$index];
        }
        return $sum;
    }

    /**
     * Count collection column average based on column index
     *
     * @param string $index
     * @param \Magento\Framework\Data\Collection $collection
     * @return float|int
     * @since 2.0.0
     */
    protected function _countAverage($index, $collection)
    {
        $itemsCount = count($collection);
        return $itemsCount ? $this->_countSum($index, $collection) / $itemsCount : $itemsCount;
    }
}
