<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Block\Grid\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

/**
 * Provides tax rates codes for each tax rule in the grid.
 */
class Codes extends AbstractRenderer
{
    /**
     * Renders rates codes grid column
     *
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $ratesCodes = $row->getTaxRatesCodes();

        return $ratesCodes && is_array($ratesCodes) ? $this->escapeHtml(implode(', ', $ratesCodes)) : '';
    }
}
