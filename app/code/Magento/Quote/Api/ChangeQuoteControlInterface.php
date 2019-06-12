<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

<<<<<<< HEAD
=======
declare(strict_types=1);

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
