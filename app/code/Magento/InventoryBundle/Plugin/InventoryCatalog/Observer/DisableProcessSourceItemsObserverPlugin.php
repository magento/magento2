<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundle\Plugin\InventoryCatalog\Observer;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\InventoryCatalog\Observer\ProcessSourceItemsObserver;
use Magento\Framework\Event\Observer;

/**
 * Class disable inventory for bundle products
 */
class DisableProcessSourceItemsObserverPlugin
{
    public function aroundExecute(
        ProcessSourceItemsObserver $subject,
        callable $proceed,
        Observer $observer
    ) {
        /** @var ProductInterface $product */
        $product = $observer->getEvent()->getProduct();
        if ($product->getTypeId() == Type::TYPE_CODE) {
            return;
        }

        $proceed($observer);
    }
}
