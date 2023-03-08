<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml tax rates grid item renderer country
 */
namespace Magento\TaxImportExport\Block\Adminhtml\Rate\Grid\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\Country as ColumnRendererCountry;
use Magento\Framework\DataObject;

class Country extends ColumnRendererCountry
{
    /**
     * Render column for export
     *
     * @param DataObject $row
     * @return string
     */
    public function renderExport(DataObject $row)
    {
        return $row->getData($this->getColumn()->getIndex());
    }
}
