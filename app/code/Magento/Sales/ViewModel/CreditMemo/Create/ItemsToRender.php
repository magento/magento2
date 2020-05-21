<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Sales\ViewModel\CreditMemo\Create;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Items;
use Magento\Sales\Model\Convert\OrderFactory;
use Magento\Sales\Model\Convert\Order as ConvertOrder;

/**
 * View model to return creditmemo items for rendering
 */
class ItemsToRender implements ArgumentInterface
{
    /**
     * @var Items
     */
    private $items;

    /**
     * @var ConvertOrder
     */
    private $converter;

    /**
     * @param Items $items
     * @param OrderFactory $convertOrderFactory
     */
    public function __construct(
        Items $items,
        OrderFactory $convertOrderFactory
    ) {
        $this->items = $items;
        $this->converter = $convertOrderFactory->create();
    }

    /**
     * Return creditmemo items for rendering and make sure all its parents are included
     *
     * @return \Magento\Sales\Model\Order\Creditmemo\Item[]
     */
    public function getItems(): array
    {
        $creditMemo = $this->items->getCreditmemo();
        $parents = [];
        $items = [];
        foreach ($creditMemo->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            if ($orderItem->getChildrenItems()) {
                $parents[] = $orderItem->getItemId();
            }
        }
        foreach ($this->items->getCreditmemo()->getAllItems() as $item) {
            $orderItemParent = $item->getOrderItem()->getParentItem();
            if ($orderItemParent && !in_array($orderItemParent->getItemId(), $parents)) {
                $itemParent = $this->converter->itemToCreditmemoItem($orderItemParent);
                $itemParent->setCreditmemo($creditMemo)
                    ->setParentId($creditMemo->getId())
                    ->setStoreId($creditMemo->getStoreId());
                $items[] = $itemParent;
                $parents[] = $orderItemParent->getItemId();
            }
            $items[] = $item;
        }
        return $items;
    }
}
