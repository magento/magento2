<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\View\Tab;

use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Info\ShippingInfoBlock;
use Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Info\CommentsHistoryBlock;
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
     * Selector for `Shipping Information` block.
     *
     * @var string
     */
    private $shippingInfoBlock = '.order-shipping-method';

    /**
     * Selector for Comments history block.
     *
     * @var string
     */
    private $commentsHistoryBlockSelector = '#order_history_block';

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
     * Gets Order Shipping Information block.
     *
     * @return ShippingInfoBlock
     */
    public function getShippingInfoBlock()
    {
        return $this->blockFactory->create(
            ShippingInfoBlock::class,
            ['element' => $this->_rootElement->find($this->shippingInfoBlock)]
        );
    }

    /**
     * Returns Comments history block.
     *
     * @return CommentsHistoryBlock
     */
    public function getCommentsHistoryBlock()
    {
        return $this->blockFactory->create(
            CommentsHistoryBlock::class,
            ['element' => $this->_rootElement->find($this->commentsHistoryBlockSelector)]
        );
    }
}
