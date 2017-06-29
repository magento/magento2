<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml tax rates grid item renderer country
 *
 * @author     Magento Core Team <core@magentocommerce.com>
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
