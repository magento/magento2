<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Weee\Helper\Data;

/**
 * Add Weee item to Payment Cart amount.
 */
class AddPaymentWeeeItem implements ObserverInterface
{
    /**
     * @var Data
     */
    private $weeeData;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Data $weeeData
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Data $weeeData,
        StoreManagerInterface $storeManager
    ) {
        $this->weeeData = $weeeData;
        $this->storeManager = $storeManager;
    }

    /**
     * Add FPT amount as custom item to payment cart totals.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($this->shouldBeAddedAsCustomItem() === false) {
            return;
        }

        /** @var \Magento\Payment\Model\Cart $cart */
        $cart = $observer->getEvent()->getCart();
        $salesEntity = $cart->getSalesModel();

        $totalWeee = 0;
        foreach ($salesEntity->getAllItems() as $item) {
            $originalItem = $item->getOriginalItem();
            if (!$originalItem->getParentItem()) {
                $totalWeee += $this->weeeData->getBaseWeeeTaxAppliedRowAmount($originalItem);
            }
        }

        if ($totalWeee > 0.0001) {
            $cart->addCustomItem(__('FPT'), 1, $totalWeee);
        }
    }

    /**
     * Checks if FPT should be added to payment cart as custom item or not.
     *
     * @return bool
     */
    private function shouldBeAddedAsCustomItem()
    {
        $storeId = $this->storeManager->getStore()->getId();

        return $this->weeeData->isEnabled($storeId) && $this->weeeData->includeInSubtotal($storeId) === false;
    }
}
