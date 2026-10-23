<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Plugin\Model\Order\Item;

use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderItemSearchResultInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Model\Order\Item\ParentSkuLoader;

/**
 * Adds the parent_sku extension attribute to items returned by the order item repository
 */
class AddParentSku
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
     * Add parent_sku to a single order item
     *
     * @param OrderItemRepositoryInterface $subject
     * @param OrderItemInterface $result
     * @return OrderItemInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(OrderItemRepositoryInterface $subject, OrderItemInterface $result): OrderItemInterface
    {
        $this->parentSkuLoader->execute([$result]);

        return $result;
    }

    /**
     * Add parent_sku to all order items in the result
     *
     * @param OrderItemRepositoryInterface $subject
     * @param OrderItemSearchResultInterface $result
     * @return OrderItemSearchResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        OrderItemRepositoryInterface $subject,
        OrderItemSearchResultInterface $result
    ): OrderItemSearchResultInterface {
        $this->parentSkuLoader->execute($result->getItems());

        return $result;
    }
}
