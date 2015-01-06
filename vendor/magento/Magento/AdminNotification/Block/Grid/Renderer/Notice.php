<?php
/**
 * Adminhtml AdminNotification Severity Renderer
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
