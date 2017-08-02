<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Block\Customer;

use Magento\Vault\Model\CreditCardTokenFactory;

/**
 * Class CreditCards
 *
 * @api
 * @since 2.1.3
 */
class CreditCards extends PaymentTokens
{
    /**
     * @inheritdoc
     * @since 2.1.3
     */
    public function getType()
    {
        return CreditCardTokenFactory::TOKEN_TYPE_CREDIT_CARD;
    }
}
