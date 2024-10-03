<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order;

/**
 * Order item quantities validation for Creditmemo creation.
 */
class CreditmemoValidator
{

    /**
     * Check if no dummy order item can be refunded
     *
     * @param Item $item
     * @param ?array $invoiceQtysRefundLimits
     * @return bool
     */
    public function canRefundNoDummyItem(Item $item, ?array $invoiceQtysRefundLimits = []): bool
    {
        if ($item->getQtyToRefund() <= 0) {
            return false;
        }
        if (isset($invoiceQtysRefundLimits[$item->getId()])) {
            return $invoiceQtysRefundLimits[$item->getId()] > 0;
        }

        return true;
    }

    /**
     * Check if order item can be refunded
     *
     * @param Item $item
     * @param ?array $qtys
     * @param ?array $invoiceQtysRefundLimits
     * @return bool
     */
    public function canRefundItem(Item $item, ?array $qtys = [], ?array $invoiceQtysRefundLimits = []): bool
    {
        if ($item->isDummy()) {
            if ($item->getHasChildren()) {
                return $this->canRefundDummyItemWithChildren($item, $qtys, $invoiceQtysRefundLimits);
            } elseif ($item->getParentItem()) {
                return $this->canRefundDummyItemWithParent($item, $qtys, $invoiceQtysRefundLimits);
            }
            return false;
        }

        return $this->canRefundNoDummyItem($item, $invoiceQtysRefundLimits);
    }

    /**
     * Check if dummy order item which has children can be refunded
     *
     * @param Item $item
     * @param array|null $qtys
     * @param array|null $invoiceQtysRefundLimits
     * @return bool
     */
    private function canRefundDummyItemWithChildren(Item $item, ?array $qtys, ?array $invoiceQtysRefundLimits): bool
    {
        foreach ($item->getChildrenItems() as $child) {
            if (empty($qtys) || (count(array_unique($qtys)) === 1 && (int)end($qtys) === 0)) {
                if ($this->canRefundNoDummyItem($child, $invoiceQtysRefundLimits)) {
                    return true;
                }
            } else {
                if (isset($qtys[$child->getId()]) && $qtys[$child->getId()] > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if dummy order item which has parent can be refunded
     *
     * @param Item $item
     * @param array|null $qtys
     * @param array|null $invoiceQtysRefundLimits
     * @return bool
     */
    private function canRefundDummyItemWithParent(Item $item, ?array $qtys, ?array $invoiceQtysRefundLimits): bool
    {
        $parent = $item->getParentItem();
        if (empty($qtys)) {
            return $this->canRefundNoDummyItem($parent, $invoiceQtysRefundLimits);
        } else {
            return isset($qtys[$parent->getId()]) && $qtys[$parent->getId()] > 0;
        }
    }
}
