<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\GiftMessage\Model;

/**
 * Gift Message Observer Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Observer extends \Magento\Framework\Object
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
     * Set gift messages to order from quote address
     *
     * @param \Magento\Framework\Object $observer
     * @return $this
     */
    public function salesEventConvertQuoteAddressToOrder($observer)
    {
        if ($observer->getEvent()->getAddress()->getGiftMessageId()) {
            $observer->getEvent()->getOrder()->setGiftMessageId(
                $observer->getEvent()->getAddress()->getGiftMessageId()
            );
        }
        return $this;
    }

    /**
     * Set gift messages to order from quote address
     *
     * @param \Magento\Framework\Object $observer
     * @return $this
     */
    public function salesEventConvertQuoteToOrder($observer)
    {
        $observer->getEvent()->getOrder()->setGiftMessageId($observer->getEvent()->getQuote()->getGiftMessageId());
        return $this;
    }

    /**
     * Duplicates giftmessage from order to quote on import or reorder
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function salesEventOrderToQuote($observer)
    {
        $order = $observer->getEvent()->getOrder();
        // Do not import giftmessage data if order is reordered
        if ($order->getReordered()) {
            return $this;
        }

        if (!$this->_giftMessageMessage->isMessagesAvailable('order', $order, $order->getStore())) {
            return $this;
        }
        $giftMessageId = $order->getGiftMessageId();
        if ($giftMessageId) {
            $giftMessage = $this->_messageFactory->create()->load($giftMessageId)->setId(null)->save();
            $observer->getEvent()->getQuote()->setGiftMessageId($giftMessage->getId());
        }

        return $this;
    }

    /**
     * Duplicates giftmessage from order item to quote item on import or reorder
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function salesEventOrderItemToQuoteItem($observer)
    {
        /** @var $orderItem \Magento\Sales\Model\Order\Item */
        $orderItem = $observer->getEvent()->getOrderItem();
        // Do not import giftmessage data if order is reordered
        $order = $orderItem->getOrder();
        if ($order && $order->getReordered()) {
            return $this;
        }

        $isAvailable = $this->_giftMessageMessage->isMessagesAvailable(
            'order_item',
            $orderItem,
            $orderItem->getStoreId()
        );
        if (!$isAvailable) {
            return $this;
        }

        /** @var $quoteItem \Magento\Sales\Model\Quote\Item */
        $quoteItem = $observer->getEvent()->getQuoteItem();
        if ($giftMessageId = $orderItem->getGiftMessageId()) {
            $giftMessage = $this->_messageFactory->create()->load($giftMessageId)->setId(null)->save();
            $quoteItem->setGiftMessageId($giftMessage->getId());
        }
        return $this;
    }
}
