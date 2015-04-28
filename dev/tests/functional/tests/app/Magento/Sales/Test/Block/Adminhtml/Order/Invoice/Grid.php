<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Invoice;

use Magento\Backend\Test\Block\Widget\Grid as GridInterface;

/**
 * Sales order grid.
 */
class Grid extends GridInterface
{
    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'id' => [
            'selector' => '#order_invoices_filter_increment_id',
        ],
    ];

    /**
     * Invoice amount.
     *
     * @var string
     */
    protected $invoiceAmount = 'td.col-qty.col-base_grand_total';

    /**
     * An element locator which allows to select entities in grid.
     *
     * @var string
     */
    protected $selectItem = 'tbody tr .col-invoice-number';

    /**
     * Get first invoice amount.
     *
     * @return string
     */
    public function getInvoiceAmount()
    {
        $invoiceAmount = $this->_rootElement->find($this->invoiceAmount)->getText();
        return $this->escapeCurrency($invoiceAmount);
    }

    /**
     * Click the first invoice amount.
     *
     * @return void
     */
    public function clickInvoiceAmount()
    {
        $this->_rootElement->find($this->invoiceAmount)->click();
    }

    /**
     * Method that escapes currency symbols.
     *
     * @param string $price
     * @return string|null
     */
    protected function escapeCurrency($price)
    {
        preg_match("/^\\D*\\s*([\\d,\\.]+)\\s*\\D*$/", $price, $matches);
        return (isset($matches[1])) ? $matches[1] : null;
    }
}
