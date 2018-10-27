<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
     * @param Quote $quote
     * @param Quote $result
     * @param mixed $itemId
     *
     * @return Quote
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRemoveItem(Quote $quote, Quote $result, $itemId): Quote
    {
        if (empty($result->getAllVisibleItems())) {
            foreach ($result->getAllAddresses() as $address) {
                $result->removeAddress($address->getId());
            }
        }

        return $result;
    }
}
