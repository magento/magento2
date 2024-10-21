<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Block\Adminhtml\Grid\Column\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\Currency as BackendCurrency;
use Magento\Framework\Currency\Exception\CurrencyException;
use Magento\Framework\DataObject;

/**
 * Adminhtml grid item renderer currency
 *
 * @api
 * @since 100.0.2
 *
 */
class Currency extends BackendCurrency
{
    /**
     * Renders grid column
     *
     * @param DataObject $row
     * @return string
     * @throws CurrencyException
     */
    public function render(DataObject $row)
    {
        $data = $row->getData($this->getColumn()->getIndex());
        $currencyCode = $this->_getCurrencyCode($row);

        if (!$currencyCode) {
            return $data;
        }
        $data = (float)$data * $this->_getRate($row);
        $data = sprintf('%f', $data);
        $data = $this->_localeCurrency->getCurrency($currencyCode)->toCurrency($data);
        return $data;
    }
}
