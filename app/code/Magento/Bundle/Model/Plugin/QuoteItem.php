<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Plugin;

use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * Plugin for Magento\Quote\Model\Quote\Item\ToOrderItem
 * @since 2.0.0
 */
class QuoteItem
{
    /**
     * Add bundle attributes to order data
     *
     * @param ToOrderItem $subject
     * @param OrderItemInterface $orderItem
     * @param AbstractItem $item
     * @param array $data
     * @return OrderItemInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function afterConvert(ToOrderItem $subject, OrderItemInterface $orderItem, AbstractItem $item, $data = [])
    {
        if ($attributes = $item->getProduct()->getCustomOption('bundle_selection_attributes')) {
            $productOptions = $orderItem->getProductOptions();
            $productOptions['bundle_selection_attributes'] = $attributes->getValue();
            $orderItem->setProductOptions($productOptions);
        }

        return $orderItem;
    }
}
