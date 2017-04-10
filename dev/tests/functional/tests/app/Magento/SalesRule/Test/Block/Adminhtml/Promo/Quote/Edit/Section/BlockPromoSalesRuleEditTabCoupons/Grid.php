<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Block\Adminhtml\Promo\Quote\Edit\Section\BlockPromoSalesRuleEditTabCoupons;

/**
 * Grid class for coupon codes.
 */
class Grid extends \Magento\Backend\Test\Block\Widget\Grid
{
    /**
     * Get rows data
     *
     * @param array $columns
     * @return array
     */
    public function getRowsData(array $columns)
    {
        $this->waitLoader();

        $data = [];
        $rows = $this->_rootElement->getElements($this->rowItem);
        foreach ($rows as $row) {
            $rowData = [];
            foreach ($columns as $columnName) {
                $rowData[$columnName] = trim($row->find('.col-' . $columnName)->getText());
            }

            $data[] = $rowData;
        }

        return $data;
    }
}
