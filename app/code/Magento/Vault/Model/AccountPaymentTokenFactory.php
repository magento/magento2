<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model;

/**
 * Class AccountPaymentTokenFactory
 * @deprecated 2.2.0
 * @see PaymentTokenFactoryInterface
 * @since 2.2.0
 */
class AccountPaymentTokenFactory extends AbstractPaymentTokenFactory
{
    /**
     * @var string
     */
    const TOKEN_TYPE_ACCOUNT = 'account';

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getType()
    {
        return self::TOKEN_TYPE_ACCOUNT;
    }
}
