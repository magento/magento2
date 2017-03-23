<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Creditmemo;

use Magento\Backend\Test\Block\Widget\Grid as GridInterface;

/**
 * Class Grid
 * Sales order grid
 *
 */
class Grid extends GridInterface
{
    /**
     * {@inheritdoc}
     */
    protected $filters = [
        'id' => [
            'selector' => '#order_creditmemos_filter_increment_id',
        ],
    ];

    /**
     * Amount refunded
     *
     * @var string
     */
    protected $amountRefunded = 'td.col-refunded.col-base_grand_total';

    /**
     * Refund status
     *
     * @var string
     */
    protected $refundStatus = 'td.col-status.col-state';

    /**
     * An element locator which allows to select entities in grid
     *
     * @var string
     */
    protected $selectItem = 'tbody tr .col-increment_id';

    /**
     * Get first refund amount
     *
     * @return array|string
     */
    public function getRefundAmount()
    {
        return $this->_rootElement->find($this->amountRefunded)->getText();
    }

    /**
     * Get first status
     *
     * @return array|string
     */
    public function getStatus()
    {
        return $this->_rootElement->find($this->refundStatus)->getText();
    }
}
