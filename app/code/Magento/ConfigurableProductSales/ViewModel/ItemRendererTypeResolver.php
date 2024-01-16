<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductSales\ViewModel;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ProductType;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\ViewModel\ItemRendererTypeResolverInterface;

/**
 * Configurable order item renderer type resolver
 */
class ItemRendererTypeResolver implements ItemRendererTypeResolverInterface, ArgumentInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(DataObject $item): ?string
    {
        $orderItem = $item->getOrderItem() ? $item->getOrderItem() : $item;
        if ($orderItem->getProductType() === ProductType::TYPE_CODE) {
            $childItem = $this->getChildOrderItem($orderItem);
            if ($childItem->getRealProductType() && $childItem->getRealProductType() !== ProductType::TYPE_CODE) {
                return $childItem->getRealProductType();
            }
        }
        return $orderItem->getProductType();
    }

    /**
     * Get child product order item
     *
     * @param Item $orderItem
     * @return Item
     */
    private function getChildOrderItem(Item $orderItem): Item
    {
        $childrenItems = $orderItem->getChildrenItems() ?: [];
        if (count($childrenItems) === 1) {
            $orderItem = reset($childrenItems);
        }

        return $orderItem;
    }
}
