<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Block\Adminhtml\Report;

use Magento\Ui\Test\Block\Adminhtml\DataGrid;

/**
 * Class Grid
 */
class Grid extends DataGrid
{
    /**
     * List of filters for grid
     * @var array
     */
    protected $filters = [
        'id' => [
            'selector' => '[name="id"]'
        ]
    ];

    /**
     * Selector for transaction ids container
     * @var string
     */
    private $txnId = '.data-grid tbody tr td:nth-child(2) div';

    /**
     * Get list of transaction ids
     * @return array
     */
    public function getTransactionIds()
    {
        $elements = $this->_rootElement->getElements($this->txnId);
        $result = [];

        foreach ($elements as $element) {
            $result[] = trim($element->getText());
        }

        return $result;
    }
}
