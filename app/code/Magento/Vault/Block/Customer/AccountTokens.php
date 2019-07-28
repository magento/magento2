<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Block\Customer;

use Magento\Vault\Model\AccountPaymentTokenFactory;

/**
 * Class AccountTokens
 *
 * @api
 * @since 100.2.0
 */
class AccountTokens extends PaymentTokens
{
    /**
     * @inheritdoc
     * @since 100.2.0
     */
    public function getType()
    {
        return AccountPaymentTokenFactory::TOKEN_TYPE_ACCOUNT;
    }
}
