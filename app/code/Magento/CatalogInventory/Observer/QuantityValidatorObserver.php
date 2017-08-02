<?php
/**
 * Product inventory data validator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\CatalogInventory\Observer\QuantityValidatorObserver
 *
 * @since 2.0.0
 */
class QuantityValidatorObserver implements ObserverInterface
{
    /**
     * @var \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator $quantityValidator
     * @since 2.0.0
     */
    protected $quantityValidator;

    /**
     * @param \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator $quantityValidator
     * @since 2.0.0
     */
    public function __construct(
        \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator $quantityValidator
    ) {
        $this->quantityValidator = $quantityValidator;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->quantityValidator->validate($observer);
    }
}
