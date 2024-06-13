<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

/**
 * @api
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @since 100.0.2
 */
class Button extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Render grid row
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $buttonType = $this->getColumn()->getButtonType();
        $buttonClass = $this->getColumn()->getButtonClass();
        return '<button' .
            ($buttonType ? ' type="' .
            $buttonType .
            '"' : '') .
            ($buttonClass ? ' class="' .
            $buttonClass .
            '"' : '') .
            '>' .
            $this->getColumn()->getHeader() .
            '</button>';
    }
}
