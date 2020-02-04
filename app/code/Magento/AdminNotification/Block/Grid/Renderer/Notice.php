<?php
declare(strict_types=1);

/**
 * Adminhtml AdminNotification Severity Renderer
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Block\Grid\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

/**
 * Renderer class for notice in the admin notifications grid
 */
class Notice extends AbstractRenderer
{
    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(DataObject $row)
    {
        return '<span class="grid-row-title">' .
            $this->escapeHtml($row->getTitle()) .
            '</span>' .
            ($row->getDescription() ? '<br />' . $this->escapeHtml($row->getDescription()) : '');
    }
}
