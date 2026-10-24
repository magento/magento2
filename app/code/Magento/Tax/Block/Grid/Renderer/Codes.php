<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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
