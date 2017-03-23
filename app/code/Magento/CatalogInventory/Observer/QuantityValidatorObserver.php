<?php
/**
 * Product inventory data validator
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Observer;

use Magento\Framework\Event\ObserverInterface;

class QuantityValidatorObserver implements ObserverInterface
{
    /**
     * @var \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator $quantityValidator
     */
    protected $quantityValidator;

    /**
     * @param \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator $quantityValidator
     */
    public function __construct(
        \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator $quantityValidator
    ) {
        $this->quantityValidator = $quantityValidator;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->quantityValidator->validate($observer);
    }
}
