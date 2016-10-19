<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesInventory\Model\Order\Creditmemo;

use Magento\Sales\Api\CreditmemoItemRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;

/**
 * Bundle Qty Value
 */
class BundleQtyValue implements QtyValueInterface
{
    /**
     * @var CreditmemoItemRepositoryInterface
     */
    private $creditmemoItemRepository;
    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * BundleQtyValue constructor.
     * @param CreditmemoItemRepositoryInterface $creditmemoItemRepository
     * @param OrderItemRepositoryInterface $orderItemRepository
     */
    public function __construct(
        CreditmemoItemRepositoryInterface $creditmemoItemRepository,
        OrderItemRepositoryInterface $orderItemRepository
    ) {
        $this->creditmemoItemRepository = $creditmemoItemRepository;
        $this->orderItemRepository = $orderItemRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function get(
        CreditmemoItemInterface $creditmemoItem,
        CreditmemoInterface $creditmemo,
        OrderItemInterface $parentOrderItem = null,
        $priceType = null
    ) {
        $qty = $creditmemoItem->getQty();
        if ($parentOrderItem && $priceType == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED) {
            $orderItem = $this->orderItemRepository->get($creditmemoItem->getOrderItemId());
            $qty = $orderItem->getQtyOrdered()
                / $parentOrderItem->getQtyOrdered()
                * $this->getItemByOrderId($creditmemo, $parentOrderItem->getId())->getQty();
        }
        return $qty;
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
}
