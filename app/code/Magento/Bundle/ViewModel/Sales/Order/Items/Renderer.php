<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\ViewModel\Sales\Order\Items;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * ViewModel for Bundle Items
 */
class Renderer implements ArgumentInterface
{
    /**
     * @var CollectionFactory
     */
    private $itemCollectionFactory;

    /**
     * @param CollectionFactory $itemCollectionFactory
     */
    public function __construct(
        CollectionFactory $itemCollectionFactory
    ) {
        $this->itemCollectionFactory = $itemCollectionFactory;
    }

    /**
     * Get Bundle Order Item Collection.
     *
     * @param int $orderId
     * @param int $parentId
     *
     * @return array|null
     */
    public function getOrderItems(int $orderId, int $parentId): ?array
    {
        $collection = $this->itemCollectionFactory->create();
        $collection->setOrderFilter($orderId);
        $collection->addFieldToFilter(
            [OrderItemInterface::ITEM_ID, OrderItemInterface::PARENT_ITEM_ID],
            [
                ['eq' => $parentId],
                ['eq' => $parentId]
            ]
        );

        $items = [];

        foreach ($collection ?? [] as $item) {
            $items[] = $item;
        }
        $collection->clear();

        return $items;
    }
}
