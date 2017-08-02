<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

/**
 * Backend grid item renderer number
 *
 * @api
 * @deprecated 2.2.0 in favour of UI component implementation
 * @since 2.0.0
 */
class Number extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var int
     * @since 2.0.0
     */
    protected $_defaultWidth = 100;

    /**
     * Returns value of the row
     *
     * @param \Magento\Framework\DataObject $row
     * @return mixed|string
     * @since 2.0.0
     */
    protected function _getValue(\Magento\Framework\DataObject $row)
    {
        $data = parent::_getValue($row);
        if ($data !== null) {
            $value = $data * 1;
            $sign = (bool)(int)$this->getColumn()->getShowNumberSign() && $value > 0 ? '+' : '';
            if ($sign) {
                $value = $sign . $value;
            }
            // fixed for showing zero in grid
            return $value ? $value : '0';
        }
        return $this->getColumn()->getDefault();
    }

    /**
     * Renders CSS
     *
     * @return string
     * @since 2.0.0
     */
    public function renderCss()
    {
        return parent::renderCss() . ' col-number';
    }
}
