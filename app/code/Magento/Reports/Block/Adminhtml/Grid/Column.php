<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Block\Adminhtml\Grid;

use Magento\Backend\Block\Widget\Grid\Column as GridColumn;

/**
 * Grid column block
 *
 * @api
 * @deprecated in favour of UI component implementation
 */
class Column extends GridColumn
{
    /**
     * Retrieve row column field value for display
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function getRowField(\Magento\Framework\DataObject $row)
    {
        $renderedValue = parent::getRowField($row);

        if ($row->getData('child_items_sku') != null) {
            $renderedValue .= ' (' . $row->getData('child_items_sku') . ')';
        }

        return $renderedValue;
    }

    /**
     * Retrieve row column field value for export
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function getRowFieldExport(\Magento\Framework\DataObject $row)
    {
        $renderedValue = parent::getRowFieldExport($row);

        if ($row->getData('child_items_sku') != null) {
            $renderedValue .= ' (' . $row->getData('child_items_sku') . ')';
        }

        return $renderedValue;
    }
}
