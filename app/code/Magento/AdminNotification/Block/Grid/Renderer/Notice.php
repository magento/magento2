<?php
/**
 * Adminhtml AdminNotification Severity Renderer
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Block\Grid\Renderer;

class Notice extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\Object $row
     * @return  string
     */
    public function render(\Magento\Framework\Object $row)
    {
        return '<span class="grid-row-title">' .
            $row->getTitle() .
            '</span>' .
            ($row->getDescription() ? '<br />' .
            $row->getDescription() : '');
    }
}
