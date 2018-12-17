<?php
/**
 * Adminhtml AdminNotification Severity Renderer
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Block\Grid\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

/**
 * Class Notice
 *
 * @package Magento\AdminNotification\Block\Grid\Renderer
 */
class Notice extends AbstractRenderer
{
    /**
     * Renders grid column
     *
     * @param   DataObject $row
     * @return  string
     */
    public function render(DataObject $row): string
    {
        return '<span class="grid-row-title">' .
            $this->escapeHtml($row->getTitle()) .
            '</span>' .
            ($row->getDescription() ? '<br />' . $this->escapeHtml($row->getDescription()) : '');
    }
}
