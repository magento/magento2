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
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;

class GetSkuFromOrderItem implements GetSkuFromOrderItemInterface
{
    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    /**
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemManagementAllowedForProductType;

    /**
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
     */
    public function __construct(
        GetSkusByProductIdsInterface $getSkusByProductIds,
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
    ) {
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
    }

    /**
     * @inheritdoc
     */
    public function execute(OrderItemInterface $orderItem): string
    {
        try {
            $itemSku = $orderItem->getSku();

            if ($this->isSourceItemManagementAllowedForProductType->execute($orderItem->getProductType())) {
                $itemSku = $this->getSkusByProductIds->execute(
                    [$orderItem->getProductId()]
                )[$orderItem->getProductId()];
            }
        } catch (NoSuchEntityException $e) {
            $itemSku = $orderItem->getSku();
        }

        return $itemSku;
    }
}
