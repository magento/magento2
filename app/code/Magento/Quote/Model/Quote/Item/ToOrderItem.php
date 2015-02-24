<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\Quote\Item;

use Magento\Framework\Object\Copy;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Api\Data\OrderItemInterfaceFactory as OrderItemFactory;
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
     * @var OrderItemFactory
     */
    protected $orderItemFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @param OrderItemFactory $orderItemFactory
     * @param Copy $objectCopyService
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        OrderItemFactory $orderItemFactory,
        Copy $objectCopyService,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->orderItemFactory = $orderItemFactory;
        $this->objectCopyService = $objectCopyService;
        $this->dataObjectHelper = $dataObjectHelper;
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

        $orderItem = $this->orderItemFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $orderItem,
            array_merge($orderItemData, $data),
            '\Magento\Sales\Api\Data\OrderItemInterface'
        );
        $orderItem->setProductOptions($options);
        if ($quoteItem->getParentItem()) {
            $orderItem->setQtyOrdered(
                $orderItemData[OrderItemInterface::QTY_ORDERED] * $quoteItem->getParentItem()->getQty()
            );
        }
        return $orderItem;
    }
}
