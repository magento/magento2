<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventorySalesApi\Model\GetSkuFromOrderItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface;

class GetSkuFromOrderItem implements GetSkuFromOrderItemInterface
{
    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    /**
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     */
    public function __construct(
        GetSkusByProductIdsInterface $getSkusByProductIds
    ) {
        $this->getSkusByProductIds = $getSkusByProductIds;
    }

    /**
     * @inheritdoc
     */
    public function execute(OrderItemInterface $orderItem): string
    {
        try {
            $itemSku = $this->getSkusByProductIds->execute(
                [$orderItem->getProductId()]
            )[$orderItem->getProductId()];
        } catch (NoSuchEntityException $e) {
            $itemSku = $orderItem->getSku();
        }

        return $itemSku;
    }
}
