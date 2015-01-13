<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function renderExport(\Magento\Framework\Object $row)
    {
        return $row->getData($this->getColumn()->getIndex());
    }
}
