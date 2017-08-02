<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

/**
 * @api
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @since 2.0.0
 */
class Button extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Render grid row
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     * @since 2.0.0
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
