<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Gift Message Observer Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class SalesEventOrderItemToQuoteItemObserver implements ObserverInterface
{
    /**
     * Gift message message
     *
     * @var \Magento\GiftMessage\Helper\Message|null
     */
    protected $_giftMessageMessage = null;

    /**
     * @var \Magento\GiftMessage\Model\MessageFactory
     */
    protected $_messageFactory;

    /**
     * @param \Magento\GiftMessage\Model\MessageFactory $messageFactory
     * @param \Magento\GiftMessage\Helper\Message $giftMessageMessage
     */
    public function __construct(
        \Magento\GiftMessage\Model\MessageFactory $messageFactory,
        \Magento\GiftMessage\Helper\Message $giftMessageMessage
    ) {
        $this->_messageFactory = $messageFactory;
        $this->_giftMessageMessage = $giftMessageMessage;
    }

    /**
     * Duplicates giftmessage from order item to quote item on import or reorder
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $orderItem \Magento\Sales\Model\Order\Item */
        $orderItem = $observer->getEvent()->getOrderItem();
        // Do not import giftmessage data if order is reordered
        $order = $orderItem->getOrder();
        if ($order && $order->getReordered()) {
            return $this;
        }

        $isAvailable = $this->_giftMessageMessage->isMessagesAllowed(
            'order_item',
            $orderItem,
            $orderItem->getStoreId()
        );
        if (!$isAvailable) {
            return $this;
        }

        /** @var $quoteItem \Magento\Quote\Model\Quote\Item */
        $quoteItem = $observer->getEvent()->getQuoteItem();
        if ($giftMessageId = $orderItem->getGiftMessageId()) {
            $giftMessage = $this->_messageFactory->create()->load($giftMessageId)->setId(null)->save();
            $quoteItem->setGiftMessageId($giftMessage->getId());
        }

        return $this;
    }
}
