<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesInventory\Model\Order;

use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;

/**
 * Class ReturnItemProcessor
 */
class ReturnItemProcessor
{
    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * @var \Magento\Sales\Api\Data\CreditmemoItemExtensionFactory
     */
    private $creditmemoItemExtensionAttributsFactory;

    /**
     * ReturnItemProcessor constructor.
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param \Magento\Sales\Api\Data\CreditmemoItemExtensionFactory $creditmemoItemExtensionAttributsFactory
     */
    public function __construct(
        OrderItemRepositoryInterface $orderItemRepository,
        \Magento\Sales\Api\Data\CreditmemoItemExtensionFactory $creditmemoItemExtensionAttributsFactory
    ) {
        $this->orderItemRepository = $orderItemRepository;
        $this->creditmemoItemExtensionAttributsFactory = $creditmemoItemExtensionAttributsFactory;
    }

    /**
     * @param CreditmemoInterface $creditmemo
     * @param int[] $returnToStockItems
     */
    public function execute(CreditmemoInterface $creditmemo, $returnToStockItems = [])
    {
        foreach ($creditmemo->getItems() as $item) {
            $qty = $item->getQty();
            $orderItem = $this->orderItemRepository->get($item->getOrderItemId());
            $parentItemId = $orderItem->getParentItemId();
            $parentItem = $parentItemId ? $this->getItemByOrderId($creditmemo, $parentItemId) : false;
            $extensionAttributes = $item->getExtensionAttributes()
                ? $item->getExtensionAttributes() : $this->creditmemoItemExtensionAttributsFactory->create();
            if ($this->canReturnItem($item, $parentItemId, $returnToStockItems, $qty)) {
                $extensionAttributes->setReturnToStock(True);
                $qty = $parentItem ? $parentItem->getQty() * $qty : $qty;
                $extensionAttributes->setReturnToStockQty($qty);

            } else {
                $extensionAttributes->setReturnToStock(False);
            }
            $item->setExtensionAttributes($extensionAttributes);
        }
    }

    /**
     * @param \Magento\Sales\Api\Data\CreditmemoInterface $creditmemo
     * @param int $parentItemId
     * @return bool|CreditmemoItemInterface
     */
    private function getItemByOrderId(\Magento\Sales\Api\Data\CreditmemoInterface $creditmemo, $parentItemId)
    {
        foreach ($creditmemo->getItems() as $item) {
            if ($item->getOrderItemId() == $parentItemId) {
                return $item;
            }
        }
        return false;
    }

    /**
     * @param \Magento\Sales\Api\Data\CreditmemoItemInterface $item
     * @param int[] $returnToStockItems
     * @param int $parentItemId
     * @param int $qty
     * @return bool
     */
    private function canReturnItem(
        \Magento\Sales\Api\Data\CreditmemoItemInterface $item,
        $parentItemId = null,
        array $returnToStockItems,
        $qty
    ) {
        return (in_array($item->getOrderItemId(), $returnToStockItems)
            || in_array($parentItemId, $returnToStockItems)
            || ($item->getExtensionAttributes() && $item->getExtensionAttributes()->getReturnToStock())
        )
        && $qty;
    }
}