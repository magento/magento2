<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\QuoteRepository\Plugin;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Quote\Model\Quote;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * The plugin checks if the user has ability to change the quote.
 */
class AccessChangeQuoteControl
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @param UserContextInterface $userContext
     */
    public function __construct(
        UserContextInterface $userContext
    ) {
        $this->userContext = $userContext;
    }

    /**
     * Checks if change quote's customer id is allowed for current user.
     *
     * @param CartRepositoryInterface $subject
     * @param Quote $quote
     * @throws StateException if Guest has customer_id or Customer's customer_id not much with user_id
     * or unknown user's type
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(CartRepositoryInterface $subject, CartInterface $quote)
    {
        if (!$this->isAllowed($quote)) {
            throw new StateException(__("Invalid state change requested"));
        }
    }

    /**
     * Checks if user is allowed to change the quote.
     *
     * @param Quote $quote
     * @return bool
     */
    private function isAllowed(Quote $quote)
    {
        switch ($this->userContext->getUserType()) {
            case UserContextInterface::USER_TYPE_CUSTOMER:
                $isAllowed = ($quote->getCustomerId() == $this->userContext->getUserId());
                break;
            case UserContextInterface::USER_TYPE_GUEST:
                $isAllowed = ($quote->getCustomerId() === null);
                break;
            case UserContextInterface::USER_TYPE_ADMIN:
            case UserContextInterface::USER_TYPE_INTEGRATION:
                $isAllowed = true;
                break;
            default:
                $isAllowed = false;
        }

        return $isAllowed;
    }
}
