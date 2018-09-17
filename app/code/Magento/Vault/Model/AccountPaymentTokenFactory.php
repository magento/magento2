<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model;

/**
 * Class AccountPaymentTokenFactory
 */
class AccountPaymentTokenFactory extends AbstractPaymentTokenFactory
{
    /**
     * @var string
     */
    const TOKEN_TYPE_ACCOUNT = 'account';

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return self::TOKEN_TYPE_ACCOUNT;
    }
}
