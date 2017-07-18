<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 *  Unset value for Special Price if passed as null
 */
class UnsetSpecialPrice implements ObserverInterface
{
    /**
     * Unset the Special Price attribute if it is null
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var  $product \Magento\Catalog\Model\Product */
        $product = $observer->getEvent()->getProduct();
        if ($product->getSpecialPrice() === null) {
            $product->setData('special_price', '');
        }

        return $this;
    }
}
