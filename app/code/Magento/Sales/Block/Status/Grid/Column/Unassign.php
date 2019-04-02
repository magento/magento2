<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Status\Grid\Column;

/**
 * @api
 * @since 100.0.2
 */
class Unassign extends \Magento\Backend\Block\Widget\Grid\Column
{
    /**
     * Add decorated action to column
     *
     * @return array
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
     */
    public function decorateAction($value, $row, $column, $isExport)
    {
        $cell = '';
        $state = $row->getState();
        if (!empty($state)) {
            $url = $this->getUrl('*/*/unassign');
            $label = __('Unassign');
            $cell = '<a href="#" data-post="'
                .$this->escapeHtmlAttr(
                    \json_encode([
                        'action' => $url,
                        'data' => ['status' => $row->getStatus(), 'state' => $row->getState()]
                    ])
                )
                .'">' . $label . '</a>';
        }
        return $cell;
    }
}
