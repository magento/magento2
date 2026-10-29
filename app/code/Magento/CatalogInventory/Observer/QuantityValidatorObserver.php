<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
