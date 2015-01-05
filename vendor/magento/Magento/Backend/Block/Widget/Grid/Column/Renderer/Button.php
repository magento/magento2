<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

class Button extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Render grid row
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function render(\Magento\Framework\Object $row)
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
