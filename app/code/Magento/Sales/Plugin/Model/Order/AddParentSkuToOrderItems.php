<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Plugin\Model\Order;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Item\ParentSkuLoader;

/**
 * Adds the parent_sku extension attribute to the items of orders returned by the order repository
 */
class AddParentSkuToOrderItems
{
    /**
     * @var ParentSkuLoader
     */
    private $parentSkuLoader;

    /**
     * @param ParentSkuLoader $parentSkuLoader
     */
    public function __construct(ParentSkuLoader $parentSkuLoader)
    {
        $this->parentSkuLoader = $parentSkuLoader;
    }

    /**
     * Add parent_sku to the items of a single order
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $result
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $result): OrderInterface
    {
        $this->parentSkuLoader->execute($result->getItems() ?? []);

        return $result;
    }

    /**
     * Add parent_sku to the items of all orders in the result
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $result
     * @return OrderSearchResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        OrderSearchResultInterface $result
    ): OrderSearchResultInterface {
        $items = [];
        foreach ($result->getItems() as $order) {
            foreach ($order->getItems() ?? [] as $item) {
                $items[] = $item;
            }
        }
        $this->parentSkuLoader->execute($items);

        return $result;
    }
}
