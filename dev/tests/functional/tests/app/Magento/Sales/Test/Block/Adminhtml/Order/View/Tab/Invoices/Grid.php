<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Invoices;

/**
 * Class Grid
 * Invoices grid on order view page
 */
class Grid extends \Magento\Backend\Test\Block\Widget\Grid
{
    /**
     * Locator value for link in action column
     *
     * @var string
     */
    protected $editLink = '[data-column="increment_id"]';

    /**
     * Locator for invoice ids
     *
     * @var string
     */
    protected $invoiceId = 'tbody td[data-column="increment_id"]';

    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [
        'id' => [
            'selector' => 'input[name="increment_id"]',
        ],
        'status' => [
            'selector' => 'select[name="state"]',
            'input' => 'select',
        ],
        'amount_from' => [
            'selector' => 'input[name="grand_total[from]"]',
        ],
        'amount_to' => [
            'selector' => 'input[name="grand_total[to]"]',
        ],
    ];

    /**
     * Get invoice ids
     *
     * @return array
     */
    public function getIds()
    {
        $result = [];
        $invoiceIds = $this->_rootElement->getElements($this->invoiceId);
        foreach ($invoiceIds as $invoiceId) {
            $result[] = trim($invoiceId->getText());
        }

        return $result;
    }
}
