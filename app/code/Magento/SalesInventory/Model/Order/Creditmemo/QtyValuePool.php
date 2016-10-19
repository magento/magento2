<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesInventory\Model\Order\Creditmemo;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;

/**
 * Class QtyValuePool
 * @package Magento\SalesInventory\Model\Order\Creditmemo
 */
class QtyValuePool
{
    /**
     * @var QtyValueInterface[]
     */
    private $qtyValues;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * QtyValuePool constructor.
     * @param ProductRepositoryInterface $productRepository
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param QtyValueInterface[] $qtyValues
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        OrderItemRepositoryInterface $orderItemRepository,
        array $qtyValues = []
    ) {

        $this->qtyValues = $qtyValues;
        $this->productRepository = $productRepository;
        $this->orderItemRepository = $orderItemRepository;
    }

    /**
     * @param CreditmemoItemInterface $creditmemoItem
     * @param CreditmemoInterface $creditmemo
     * @param int|null $parentItemId
     * @return float
     */
    public function get(CreditmemoItemInterface $creditmemoItem, CreditmemoInterface $creditmemo, $parentItemId = null)
    {
        $parentOrderItem = $parentItemId ? $this->orderItemRepository->get($parentItemId) : null;
        $productId = $parentOrderItem  ? $parentOrderItem->getProductId() : $creditmemoItem->getProductId();
        $product =  $this->productRepository->getById($productId);

        if (!isset($this->qtyValues[$product->getTypeId()])) {
            $this->qtyValues[$product->getTypeId()] = $this->qtyValues[
                \Magento\Catalog\Model\Product\Type::DEFAULT_TYPE
            ];
        }

        return $this->qtyValues[$product->getTypeId()]->get(
            $creditmemoItem,
            $creditmemo,
            $parentOrderItem,
            $product->getPriceType()
        );
    }
}
