<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\View\Tab;

use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Info\CommentHistoryBlock;
use Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Info\PaymentInfoBlock;

/**
 * Order information tab block.
 */
class Info extends Tab
{
    /**
     * Order status selector.
     *
     * @var string
     */
    protected $orderStatus = '#order_status';

    /**
     * Selector for 'Payment Information' block.
     *
     * @var string
     */
    private $paymentInfoBlockSelector = '.order-payment-method';

    /**
     * Selector for Comment history block.
     *
     * @var string
     */
    private $commentHistoryBlockSelector = '#order_history_block';

    /**
     * Get order status from info block.
     *
     * @return array|string
     */
    public function getOrderStatus()
    {
        return $this->_rootElement->find($this->orderStatus)->getText();
    }

    /**
     * Returns Payment Information block.
     *
     * @return PaymentInfoBlock
     */
    public function getPaymentInfoBlock()
    {
        return $this->blockFactory->create(
            PaymentInfoBlock::class,
            ['element' => $this->_rootElement->find($this->paymentInfoBlockSelector)]
        );
    }

    /**
     * Returns Comment history block.
     *
     * @return CommentHistoryBlock
     */
    public function getCommentHistoryBlock()
    {
        return $this->blockFactory->create(
            CommentHistoryBlock::class,
            ['element' => $this->_rootElement->find($this->commentHistoryBlockSelector)]
        );
    }
}
