<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Block\Adminhtml\Grid\Column\Renderer;

/**
 * Adminhtml grid item renderer currency
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @api
 * @since 2.0.0
 */
class Currency extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Currency
{
    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     * @since 2.0.0
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $data = $row->getData($this->getColumn()->getIndex());
        $currencyCode = $this->_getCurrencyCode($row);

        if (!$currencyCode) {
            return $data;
        }

        $data = floatval($data) * $this->_getRate($row);
        $data = sprintf("%f", $data);
        $data = $this->_localeCurrency->getCurrency($currencyCode)->toCurrency($data);
        return $data;
    }
}
