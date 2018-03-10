<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\SalesQuoteItem;

use Magento\Framework\Event\Observer;

/**
 * Responsible for validation quantity on add to cart action
 *
 * @api
 */
interface QuantityValidatorInterface
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function validate(Observer $observer);
}
