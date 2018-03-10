<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Observer\SalesQuoteItem;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\InventorySales\Model\SalesQuoteItem\QuantityValidatorInterface;

class QuantityValidatorObserver implements ObserverInterface
{
    /**
     * @var QuantityValidatorInterface
     */
    private $quantityValidator;

    /**
     * @param QuantityValidatorInterface $quantityValidator
     */
    public function __construct(
        QuantityValidatorInterface $quantityValidator
    ) {
        $this->quantityValidator = $quantityValidator;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $this->quantityValidator->validate($observer);
    }
}
