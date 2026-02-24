<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Plugin\Model\Quote;

use Magento\Quote\Model\Quote;

/**
 * Clear quote addresses after all items were removed.
 */
class ResetQuoteAddresses
{
    /**
     * Clears the quote addresses when all the items are removed from the cart
     *
     * @param Quote $quote
     * @param Quote $result
     * @param mixed $itemId
     * @return Quote
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRemoveItem(Quote $quote, Quote $result, $itemId): Quote
    {
        if (empty($result->getAllVisibleItems())) {
            foreach ($result->getAllAddresses() as $address) {
                $result->removeAddress($address->getId());
            }
            $extensionAttributes = $result->getExtensionAttributes();
            if (!$result->isVirtual() && $extensionAttributes && $extensionAttributes->getShippingAssignments()) {
                $extensionAttributes->setShippingAssignments([]);
            }
        }

        return $result;
    }
}
