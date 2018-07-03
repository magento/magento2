<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\QuoteRepository\Plugin;

use Magento\Quote\Api\ChangeQuoteControlInterface;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * The plugin checks if the user has ability to change the quote.
 */
class AccessChangeQuoteControl
{
    /**
     * @var ChangeQuoteControlInterface $changeQuoteControl
     */
    private $changeQuoteControl;

    /**
     * @param ChangeQuoteControlInterface $changeQuoteControl
     */
    public function __construct(ChangeQuoteControlInterface $changeQuoteControl)
    {
        $this->changeQuoteControl = $changeQuoteControl;
    }

    /**
     * Checks if change quote's customer id is allowed for current user.
     *
     * @param CartRepositoryInterface $subject
     * @param CartInterface $quote
     * @throws StateException if Guest has customer_id or Customer's customer_id not much with user_id
     * or unknown user's type
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(CartRepositoryInterface $subject, CartInterface $quote)
    {
        if (! $this->changeQuoteControl->isAllowed($quote)) {
            throw new StateException(__("Invalid state change requested"));
        }
    }
}
