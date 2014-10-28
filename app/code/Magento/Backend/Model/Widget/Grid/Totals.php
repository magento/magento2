<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Model\Widget\Grid;

class Totals extends \Magento\Backend\Model\Widget\Grid\AbstractTotals
{
    /**
     * Count collection column sum based on column index
     *
     * @param string $index
     * @param \Magento\Framework\Data\Collection $collection
     * @return float|int
     */
    protected function _countSum($index, $collection)
    {
        $sum = 0;
        foreach ($collection as $item) {
            if (!$item->hasChildren()) {
                $sum += $item[$index];
            } else {
                $sum += $this->_countSum($index, $item->getChildren());
            }
        }
        return $sum;
    }

    /**
     * Count collection column average based on column index
     *
     * @param string $index
     * @param \Magento\Framework\Data\Collection $collection
     * @return float|int
     */
    protected function _countAverage($index, $collection)
    {
        $itemsCount = 0;
        foreach ($collection as $item) {
            if (!$item->hasChildren()) {
                $itemsCount += 1;
            } else {
                $itemsCount += count($item->getChildren());
            }
        }

        return $itemsCount ? $this->_countSum($index, $collection) / $itemsCount : $itemsCount;
    }
}
