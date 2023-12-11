<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OfflineShipping\Model\Quote\Address;

use Magento\OfflineShipping\Model\SalesRule\Calculator;
use Magento\OfflineShipping\Model\SalesRule\ExtendedCalculator;
use Magento\Quote\Model\Quote\Address\FreeShippingInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Store\Model\StoreManagerInterface;

class FreeShipping implements FreeShippingInterface
{
    /**
     * @var ExtendedCalculator
     */
    protected $calculator;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     * @param Calculator $calculator
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Calculator $calculator
    ) {
        $this->storeManager = $storeManager;
        $this->calculator = $calculator;
    }

    /**
     * {@inheritDoc}
     */
    public function isFreeShipping(\Magento\Quote\Model\Quote $quote, $items)
    {
        /** @var \Magento\Quote\Api\Data\CartItemInterface[] $items */
        if (!count($items)) {
            return false;
        }

        $result = false;
        $addressFreeShipping = true;
        $store = $this->storeManager->getStore($quote->getStoreId());
        $this->calculator->init(
            $store->getWebsiteId(),
            $quote->getCustomerGroupId(),
            $quote->getCouponCode()
        );
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setFreeShipping(0);
        /** @var \Magento\Quote\Api\Data\CartItemInterface $item */
        foreach ($items as $item) {
            if ($item->getNoDiscount()) {
                $addressFreeShipping = false;
                $item->setFreeShipping(false);
                continue;
            }

            /** Child item discount we calculate for parent */
            if ($item->getParentItemId()) {
                continue;
            }

            $this->calculator->processFreeShipping($item);
            // at least one item matches to the rule and the rule mode is not a strict
            if ((bool)$item->getAddress()->getFreeShipping()) {
                $result = true;
                break;
            }

            $itemFreeShipping = (bool)$item->getFreeShipping();
            $addressFreeShipping = $addressFreeShipping && $itemFreeShipping;
            $result = $addressFreeShipping;
        }

        $shippingAddress->setFreeShipping((int)$result);
        $this->applyToItems($items, $result);
        return $result;
    }

    /**
     * @param AbstractItem $item
     * @param bool $isFreeShipping
     * @return void
     */
    protected function applyToChildren(\Magento\Quote\Model\Quote\Item\AbstractItem $item, $isFreeShipping)
    {
        if ($item->getHasChildren() && $item->isChildrenCalculated()) {
            foreach ($item->getChildren() as $child) {
                $this->calculator->processFreeShipping($child);
                if ($isFreeShipping) {
                    $child->setFreeShipping($isFreeShipping);
                }
            }
        }
    }

    /**
     * Sets free shipping availability to the quote items.
     *
     * @param array $items
     * @param bool $freeShipping
     */
    private function applyToItems(array $items, bool $freeShipping)
    {
        /** @var AbstractItem $item */
        foreach ($items as $item) {
            $item->getAddress()
                ->setFreeShipping((int)$freeShipping);
            $this->applyToChildren($item, $freeShipping);
        }
    }
}
