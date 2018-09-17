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
                $item->setFreeShipping(false);
                continue;
            }

            /** Child item discount we calculate for parent */
            if ($item->getParentItemId()) {
                continue;
            }

            $this->calculator->processFreeShipping($item);
            $itemFreeShipping = (bool)$item->getFreeShipping();

            /**  Removed below as it is set in Calculator.
             *
             * $addressFreeShipping = $addressFreeShipping && $itemFreeShipping;
             *
             * if ($addressFreeShipping && !$item->getAddress()->getFreeShipping()) {
             *   $item->getAddress()->setFreeShipping(true);
             * }
             *
             */
             /** Parent free shipping we apply to all children*/
            $this->applyToChildren($item, $itemFreeShipping);
        }
        return (bool)$shippingAddress->getFreeShipping();
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
}
