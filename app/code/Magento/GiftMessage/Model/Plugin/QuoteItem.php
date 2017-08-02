<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Model\Plugin;

use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\GiftMessage\Helper\Message as MessageHelper;
use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * Class \Magento\GiftMessage\Model\Plugin\QuoteItem
 *
 * @since 2.0.0
 */
class QuoteItem
{
    /**
     * @var MessageHelper
     * @since 2.0.0
     */
    protected $_helper;

    /**
     * @param MessageHelper $helper
     * @since 2.0.0
     */
    public function __construct(MessageHelper $helper)
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
     * @since 2.2.0
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
