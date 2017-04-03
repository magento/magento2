<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Block\Customer;

use Magento\Vault\Model\AccountPaymentTokenFactory;

/**
 * Class AccountTokens
 */
class AccountTokens extends PaymentTokens
{
    /**
     * @inheritdoc
     */
    public function getType()
    {
        return AccountPaymentTokenFactory::TOKEN_TYPE_ACCOUNT;
    }
}
