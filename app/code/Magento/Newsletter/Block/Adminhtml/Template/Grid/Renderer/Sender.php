<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Newsletter templates grid block sender item renderer
 */
namespace Magento\Newsletter\Block\Adminhtml\Template\Grid\Renderer;

/**
 * Class Newsletter Grid Renderer Sender
 */
class Sender extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Renderer for "Action" column in Newsletter templates grid.
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $str = '';
        if ($row->getTemplateSenderName()) {
            $str .= $this->escapeHtml($row->getTemplateSenderName()) . ' ';
        }
        if ($row->getTemplateSenderEmail()) {
            $str .= '[' . $this->escapeHtml($row->getTemplateSenderEmail()) . ']';
        }
        if ($str == '') {
            $str .= '---';
        }

        return $str;
    }
}
