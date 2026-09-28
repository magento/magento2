<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

/**
 * Adminhtml tax rates grid item renderer country
 */
namespace Magento\TaxImportExport\Block\Adminhtml\Rate\Grid\Renderer;

class Country extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Country
{
    /**
     * Render column for export
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function renderExport(\Magento\Framework\DataObject $row)
    {
        return $row->getData($this->getColumn()->getIndex());
    }
}
