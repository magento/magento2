<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Sales\Order\Plugin;

/**
 * Plugin to calculate bundle item qty available for cancel
 * @since 2.2.0
 */
class Item
{
    /**
     * Retrieve item qty available for cancel
     *
     * @param \Magento\Sales\Model\Order\Item $subject
     * @param float|integer $result
     * @return float|integer
     * @since 2.2.0
     */
    public function afterGetQtyToCancel(\Magento\Sales\Model\Order\Item $subject, $result)
    {
        if ($subject->getProductType() === \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE || $subject->getParentItem()
            && $subject->getParentItem()->getProductType() === \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
        ) {
            $qtyToCancel = $this->getQtyToCancelBundle($subject);
            return max($qtyToCancel, 0);
        }
        return $result;
    }

    /**
     * Retrieve item qty available for ship
     *
     * @param \Magento\Sales\Model\Order\Item $subject
     * @param float|integer $result
     * @return bool
     * @since 2.2.0
     */
    public function afterIsProcessingAvailable(\Magento\Sales\Model\Order\Item $subject, $result)
    {
        if ($subject->getProductType() === \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE || $subject->getParentItem()
            && $subject->getParentItem()->getProductType() === \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
        ) {
            return $subject->getSimpleQtyToShip() > $subject->getQtyToCancel();
        }
        return $result;
    }

    /**
     * Retrieve Bundle child item qty available for cancel
     * getQtyToShip() always returns 0 for BundleItems that ship together
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return float|integer
     * @since 2.2.0
     */
    private function getQtyToCancelBundle($item)
    {
        if ($item->isDummy(true)) {
            return min($item->getQtyToInvoice(), $item->getSimpleQtyToShip());
        }
        return min($item->getQtyToInvoice(), $item->getQtyToShip());
    }
}
