<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Vault\Api\Data;

/**
 * Interface PaymentTokenFactoryInterface
 * @api
 */
interface PaymentTokenFactoryInterface
{
    /**
     * Create payment token entity
     * @return PaymentTokenInterface
     */
    public function create();

    /**
     * Return type of payment token
     * @return string
     */
    public function getType();
}
