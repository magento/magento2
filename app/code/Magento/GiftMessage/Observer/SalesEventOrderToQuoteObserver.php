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
 * @since 2.0.0
 */
class SalesEventOrderToQuoteObserver implements ObserverInterface
{
    /**
     * Gift message message
     *
     * @var \Magento\GiftMessage\Helper\Message|null
     * @since 2.0.0
     */
    protected $_giftMessageMessage = null;

    /**
     * @var \Magento\GiftMessage\Model\MessageFactory
     * @since 2.0.0
     */
    protected $_messageFactory;

    /**
     * @param \Magento\GiftMessage\Model\MessageFactory $messageFactory
     * @param \Magento\GiftMessage\Helper\Message $giftMessageMessage
     * @since 2.0.0
     */
    public function __construct(
        \Magento\GiftMessage\Model\MessageFactory $messageFactory,
        \Magento\GiftMessage\Helper\Message $giftMessageMessage
    ) {
        $this->_messageFactory = $messageFactory;
        $this->_giftMessageMessage = $giftMessageMessage;
    }

    /**
     * Duplicates giftmessage from order to quote on import or reorder
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        // Do not import giftmessage data if order is reordered
        if ($order->getReordered()) {
            return $this;
        }

        if (!$this->_giftMessageMessage->isMessagesAllowed('order', $order, $order->getStore())) {
            return $this;
        }
        $giftMessageId = $order->getGiftMessageId();
        if ($giftMessageId) {
            $giftMessage = $this->_messageFactory->create()->load($giftMessageId)->setId(null)->save();
            $observer->getEvent()->getQuote()->setGiftMessageId($giftMessage->getId());
        }

        return $this;
    }
}
