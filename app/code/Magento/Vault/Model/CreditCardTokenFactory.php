<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model;

/**
 * Class CreditCardTokenFactory
 * @deprecated 2.2.0
 * @see PaymentTokenFactoryInterface
 * @since 2.1.3
 */
class CreditCardTokenFactory extends AbstractPaymentTokenFactory
{
    /**
     * @var string
     */
    const TOKEN_TYPE_CREDIT_CARD = 'card';

    /**
     * @inheritdoc
     * @since 2.1.3
     */
    public function getType()
    {
        return self::TOKEN_TYPE_CREDIT_CARD;
    }
}
