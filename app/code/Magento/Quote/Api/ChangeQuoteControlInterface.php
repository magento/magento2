<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Api;

use Magento\Quote\Api\Data\CartInterface;

/**
 * Service checks if the user has ability to change the quote.
 *
 * @api
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
