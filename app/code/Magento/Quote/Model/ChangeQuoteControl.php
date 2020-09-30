<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Quote\Api\ChangeQuoteControlInterface;
use Magento\Quote\Api\Data\CartInterface;

class ChangeQuoteControl implements ChangeQuoteControlInterface
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @param UserContextInterface $userContext
     */
    public function __construct(UserContextInterface $userContext)
    {
        $this->userContext = $userContext;
    }

    /**
     * @inheritdoc
     */
    public function isAllowed(CartInterface $quote): bool
    {
        switch ($this->userContext->getUserType()) {
            case UserContextInterface::USER_TYPE_CUSTOMER:
                return ($quote->getCustomerId() == $this->userContext->getUserId());
            case UserContextInterface::USER_TYPE_GUEST:
                return ($quote->getCustomerId() === null);
            case UserContextInterface::USER_TYPE_ADMIN:
            case UserContextInterface::USER_TYPE_INTEGRATION:
                return true;
        }

        return false;
    }
}
