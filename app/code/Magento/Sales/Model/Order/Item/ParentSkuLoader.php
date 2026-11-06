<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order\Item;

use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Sales\Api\Data\OrderItemExtensionFactory;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\ResourceModel\Order\Item as OrderItemResource;

/**
 * Adds the catalog SKU of the ordered product to order items that have child items
 *
 * The SKU of a parent order item, for example of a configurable product, is the SKU of the purchased child
 * product. The parent_sku extension attribute exposes the SKU of the product the item's product_id refers to.
 */
class ParentSkuLoader
{
    /**
     * @var OrderItemResource
     */
    private $orderItemResource;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var OrderItemExtensionFactory
     */
    private $extensionFactory;

    /**
     * @param OrderItemResource $orderItemResource
     * @param ProductResource $productResource
     * @param OrderItemExtensionFactory $extensionFactory
     */
    public function __construct(
        OrderItemResource $orderItemResource,
        ProductResource $productResource,
        OrderItemExtensionFactory $extensionFactory
    ) {
        $this->orderItemResource = $orderItemResource;
        $this->productResource = $productResource;
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * Set the parent_sku extension attribute on the given items that have child items
     *
     * @param OrderItemInterface[] $items
     * @return void
     */
    public function execute(array $items): void
    {
        $candidates = [];
        foreach ($items as $item) {
            if ($item->getItemId() && !$item->getParentItemId() && $item->getProductId()) {
                $candidates[(int)$item->getItemId()] = $item;
            }
        }
        if (!$candidates) {
            return;
        }

        $parents = array_intersect_key(
            $candidates,
            array_flip($this->getItemIdsWithChildren(array_keys($candidates)))
        );
        if (!$parents) {
            return;
        }

        $productIds = array_unique(
            array_map(static fn (OrderItemInterface $item): int => (int)$item->getProductId(), $parents)
        );
        $skus = array_column($this->productResource->getProductsSku($productIds), 'sku', 'entity_id');

        foreach ($parents as $item) {
            $extensionAttributes = $item->getExtensionAttributes() ?? $this->extensionFactory->create();
            $extensionAttributes->setParentSku($skus[$item->getProductId()] ?? null);
            $item->setExtensionAttributes($extensionAttributes);
        }
    }

    /**
     * Get the IDs of the given order items that are the parent of at least one other order item
     *
     * @param int[] $itemIds
     * @return int[]
     */
    private function getItemIdsWithChildren(array $itemIds): array
    {
        $connection = $this->orderItemResource->getConnection();
        $select = $connection->select()
            ->distinct()
            ->from($this->orderItemResource->getMainTable(), ['parent_item_id'])
            ->where('parent_item_id IN (?)', $itemIds, \Zend_Db::INT_TYPE);

        return array_map('intval', $connection->fetchCol($select));
    }
}
