<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreFile
namespace Magento\Vault\Api\Data;

/**
 * Interface PaymentTokenInterfaceFactory
 * @deprecated 2.2.0
 * @see PaymentTokenFactoryInterface
 * @since 2.1.3
 */
interface PaymentTokenInterfaceFactory
{
    /**
     * Create payment token entity
     * @return PaymentTokenInterface
     * @since 2.1.3
     */
    public function create();

    /**
     * Return type of payment token
     * @return string
     * @since 2.1.3
     */
    public function getType();
}
