<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Model\Plugin;

use Closure;
use Magento\Sales\Model\Order\Item;

class QuoteItem
{
    /**
     * @var \Magento\GiftMessage\Helper\Message
     */
    protected $_helper;

    /**
     * @param \Magento\GiftMessage\Helper\Message $helper
     */
    public function __construct(\Magento\GiftMessage\Helper\Message $helper)
    {
        $this->_helper = $helper;
    }

    /**
     * @param \Magento\Sales\Model\Convert\Quote $subject
     * @param Closure $proceed
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return Item
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundItemToOrderItem(
        \Magento\Sales\Model\Convert\Quote $subject,
        Closure $proceed,
        \Magento\Sales\Model\Quote\Item\AbstractItem $item
    ) {
        /** @var $orderItem Item */
        $orderItem = $proceed($item);
        $isAvailable = $this->_helper->isMessagesAvailable('item', $item, $item->getStoreId());

        $orderItem->setGiftMessageId($item->getGiftMessageId());
        $orderItem->setGiftMessageAvailable($isAvailable);
        return $orderItem;
    }
}
