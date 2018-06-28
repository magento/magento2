<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Api;

use Magento\Quote\Api\Data\CartInterface;

/**
 * Service checks if the user has ability to change the quote.
 */
interface ChangeQuoteControlInterface
{
    /**
     * Checks if user is allowed to change the quote.
     *
     * @param CartInterface $quote
     * @return bool
     */
    public function isAllowed(CartInterface $quote): bool;
}
