<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Model\Plugin;

use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\GiftMessage\Helper\Message as HelperMessage;
use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class QuoteItem
{
    /**
     * @var HelperMessage
     */
    protected $_helper;

    /**
     * @param HelperMessage $helper
     */
    public function __construct(HelperMessage $helper)
    {
        $this->_helper = $helper;
    }

    /**
     * Apply gift message per every item in order if available
     *
     * @param ToOrderItem $subject
     * @param OrderItemInterface $orderItem
     * @param AbstractItem $item
     * @param array $additional
     * @return OrderItemInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterConvert(
        ToOrderItem $subject,
        OrderItemInterface $orderItem,
        AbstractItem $item,
        $additional = []
    ) {
        $isAvailable = $this->_helper->isMessagesAllowed('item', $item, $item->getStoreId());

        $orderItem->setGiftMessageId($item->getGiftMessageId());
        $orderItem->setGiftMessageAvailable($isAvailable);
        return $orderItem;
    }
}
