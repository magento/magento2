<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
     * @var OrderItemBuilder|\Magento\Framework\Api\Builder
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
     * @param Item $object
     * @param array $data
     * @return OrderItemInterface
     */
    public function convert(Item $object, $data = [])
    {
        $orderItemData = $this->objectCopyService->getDataFromFieldset(
            'quote_convert_item',
            'to_order_item',
            $object
        );

        return $this->orderItemBuilder
            ->populateWithArray(array_merge($orderItemData, $data))
            ->create();
    }
}
