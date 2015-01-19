<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\Quote\Item;

use Magento\Framework\Object\Copy;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Api\Data\OrderItemDataBuilder as OrderItemBuilder;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * Class ToOrderItem
 */
class ToOrderItem
{
    /**
     * @var Copy
     */
    protected $objectCopyService;

    /**
     * @var OrderItemBuilder
     */
    protected $orderItemBuilder;

    /**
     * @param OrderItemBuilder $orderItemBuilder
     * @param Copy $objectCopyService
     */
    public function __construct(
        OrderItemBuilder $orderItemBuilder,
        Copy $objectCopyService
    ) {
        $this->orderItemBuilder = $orderItemBuilder;
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * @param Item $quoteItem
     * @param array $data
     * @return OrderItemInterface
     */
    public function convert(Item $quoteItem, $data = [])
    {
        $options = $quoteItem->getProductOrderOptions();
        if (!$options) {
            $options = $quoteItem->getProduct()->getTypeInstance()->getOrderOptions($quoteItem->getProduct());
        }
        $orderItemData = $this->objectCopyService->getDataFromFieldset(
            'quote_convert_item',
            'to_order_item',
            $quoteItem
        );
        if (!$quoteItem->getNoDiscount()) {
            $data = array_merge(
                $data,
                $this->objectCopyService->getDataFromFieldset(
                    'quote_convert_item',
                    'to_order_item_discount',
                    $quoteItem
                )
            );
        }
        $this->orderItemBuilder->populateWithArray(array_merge($orderItemData, $data));

        $this->orderItemBuilder->setProductOptions($options);

        if ($quoteItem->getParentItem()) {
            $this->orderItemBuilder->setQtyOrdered(
                $orderItemData[OrderItemInterface::QTY_ORDERED] * $quoteItem->getParentItem()->getQty()
            );
        }

        return $this->orderItemBuilder->create();
    }
}
