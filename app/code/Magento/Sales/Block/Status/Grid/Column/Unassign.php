<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Status\Grid\Column;

/**
 * @api
 * @since 2.0.0
 */
class Unassign extends \Magento\Backend\Block\Widget\Grid\Column
{
    /**
     * Add decorated action to column
     *
     * @return array
     * @since 2.0.0
     */
    public function getFrameCallback()
    {
        return [$this, 'decorateAction'];
    }

    /**
     * Decorate values to column
     *
     * @param string $value
     * @param \Magento\Sales\Model\Order\Status $row
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @param bool $isExport
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function decorateAction($value, $row, $column, $isExport)
    {
        $cell = '';
        $state = $row->getState();
        if (!empty($state)) {
            $url = $this->getUrl('*/*/unassign', ['status' => $row->getStatus(), 'state' => $row->getState()]);
            $label = __('Unassign');
            $cell = '<a href="' . $url . '">' . $label . '</a>';
        }
        return $cell;
    }
}
