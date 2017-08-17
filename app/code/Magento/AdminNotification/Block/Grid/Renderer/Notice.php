<?php
/**
 * Adminhtml AdminNotification Severity Renderer
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Block\Grid\Renderer;

/**
 * Class \Magento\AdminNotification\Block\Grid\Renderer\Notice
 *
 */
class Notice extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return '<span class="grid-row-title">' .
            $this->escapeHtml($row->getTitle()) .
            '</span>' .
            ($row->getDescription() ? '<br />' . $this->escapeHtml($row->getDescription()) : '');
    }
}
