<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Vault\Api\Data;

/**
 * Interface PaymentTokenFactoryInterface
 * @api
 * @since 2.2.0
 */
interface PaymentTokenFactoryInterface
{
    /**
     * Payment Token types
     * @var string
     */
    const TOKEN_TYPE_ACCOUNT = 'account';
    const TOKEN_TYPE_CREDIT_CARD = 'card';

    /**
     * Create payment token entity
     * @param $type string|null
     * @return PaymentTokenInterface
     * @since 2.2.0
     */
    public function create($type = null);
}
