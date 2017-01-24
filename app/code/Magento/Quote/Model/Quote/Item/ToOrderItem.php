<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\Quote\Item;

use Magento\Framework\DataObject\Copy;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Address\Item as AddressItem;
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
     * @param Item|AddressItem $item
     * @param array $data
     * @return OrderItemInterface
     */
    public function convert($item, $data = [])
    {
        $options = $item->getProductOrderOptions();
        if (!$options) {
            $options = $item->getProduct()->getTypeInstance()->getOrderOptions($item->getProduct());
        }
        $orderItemData = $this->objectCopyService->getDataFromFieldset(
            'quote_convert_item',
            'to_order_item',
            $item
        );
        if (!$item->getNoDiscount()) {
            $data = array_merge(
                $data,
                $this->objectCopyService->getDataFromFieldset(
                    'quote_convert_item',
                    'to_order_item_discount',
                    $item
                )
            );
        }

        $orderItem = $this->orderItemFactory->create();
        $this->objectCopyService->copyFieldsetToTarget('quote_convert_item', 'to_order_item', $item, $orderItem);
        $this->dataObjectHelper->populateWithArray(
            $orderItem,
            array_merge($orderItemData, $data),
            \Magento\Sales\Api\Data\OrderItemInterface::class
        );
        $orderItem->setProductOptions($options);
        if ($item->getParentItem()) {
            $orderItem->setQtyOrdered(
                $orderItemData[OrderItemInterface::QTY_ORDERED] * $item->getParentItem()->getQty()
            );
        }
        return $orderItem;
    }
}
