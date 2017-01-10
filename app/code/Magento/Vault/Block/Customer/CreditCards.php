<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Block\Customer;

use Magento\Vault\Model\CreditCardTokenFactory;

/**
 * Class CreditCards
 */
class CreditCards extends PaymentTokens
{
    /**
     * @inheritdoc
     */
    public function getType()
    {
        return CreditCardTokenFactory::TOKEN_TYPE_CREDIT_CARD;
    }
}
