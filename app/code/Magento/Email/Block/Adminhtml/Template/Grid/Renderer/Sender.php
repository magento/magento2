<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Email\Block\Adminhtml\Template\Grid\Renderer;

/**
 * Adminhtml system templates grid block sender item renderer
 */
class Sender extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Render grid column
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
            $str .= '[' . $row->getTemplateSenderEmail() . ']';
        }

        if ($str == '') {
            $str .= '---';
        }

        return $str;
    }
}
