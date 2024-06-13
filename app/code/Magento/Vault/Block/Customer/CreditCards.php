<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Vault\Block\Customer;

use Magento\Vault\Model\CreditCardTokenFactory;

/**
 * Class CreditCards
 *
 * @api
 * @since 100.2.0
 */
class CreditCards extends PaymentTokens
{
    /**
     * @inheritdoc
     * @since 100.2.0
     */
    public function getType()
    {
        return CreditCardTokenFactory::TOKEN_TYPE_CREDIT_CARD;
    }
}
