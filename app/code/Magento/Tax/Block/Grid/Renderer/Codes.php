<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Block\Grid\Renderer;

/**
 * Provides tax rates codes for each tax rule in the grid.
 */
class Codes extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Renders rates codes grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $ratesCodes = $row->getTaxRatesCodes();

        return $ratesCodes && is_array($ratesCodes) ? $this->escapeHtml(implode(', ', $ratesCodes)) : '';
    }
}
