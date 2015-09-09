<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflineShipping\Model\Quote;

use Magento\Quote\Model\Quote\Address;

class Freeshipping extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Magento\OfflineShipping\Model\SalesRule\Calculator
     */
    protected $calculator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\OfflineShipping\Model\SalesRule\Calculator $calculator
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\OfflineShipping\Model\SalesRule\Calculator $calculator
    ) {
        $this->setCode('freeshipping');
        $this->storeManager = $storeManager;
        $this->calculator = $calculator;
    }

    /**
     * Collect information about free shipping for all address items
     *
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param Address\Total $total
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function collect(
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($shippingAssignment, $total);
        $quote = $shippingAssignment->getShipping()->getAddress()->getQuote();
        $store = $this->storeManager->getStore($quote->getStoreId());

        /** @var \Magento\Quote\Api\Data\AddressInterface $address */
        $address = $shippingAssignment->getShipping()->getAddress();
        $address->setFreeShipping(0);

        if (!count($shippingAssignment->getItems())) {
            return $this;
        }

        $this->calculator->init($store->getWebsiteId(), $quote->getCustomerGroupId(), $quote->getCouponCode());
        $isAllFree = true;

        foreach ($shippingAssignment->getItems() as $item) {
            if ($item->getNoDiscount()) {
                $isAllFree = false;
                $item->setFreeShipping(false);
            } else {
                /**
                 * Child item discount we calculate for parent
                 */
                if ($item->getParentItemId()) {
                    continue;
                }
                $this->calculator->processFreeShipping($item);
                $isItemFree = (bool)$item->getFreeShipping();
                $isAllFree = $isAllFree && $isItemFree;
                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    foreach ($item->getChildren() as $child) {
                        $this->calculator->processFreeShipping($child);
                        /**
                         * Parent free shipping we apply to all children
                         */
                        if ($isItemFree) {
                            $child->setFreeShipping($isItemFree);
                        }
                    }
                }
            }
        }
        if ($isAllFree && !$address->getFreeShipping()) {
            $address->setFreeShipping(true);
        }
        return $this;
    }

    /**
     * Add information about free shipping for all address items to address object
     * By default we not present such information
     *
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(\Magento\Quote\Model\Quote\Address\Total $total)
    {
        return [];
    }
}
