<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Block\Grid\Renderer;

/**
 * Provides tax rates codes for each tax rule in the grid.
 * @since 2.2.0
 */
class Codes extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Renders rates codes grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     * @since 2.2.0
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $ratesCodes = $row->getTaxRatesCodes();

        return is_array($ratesCodes) ? implode(', ', $ratesCodes) : '';
    }
}
