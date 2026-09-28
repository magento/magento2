<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Vault\Api\Data;

/**
 * Interface PaymentTokenInterfaceFactory
 * @deprecated 101.0.0
 * @see PaymentTokenFactoryInterface
 * @codingStandardsIgnoreStart
 */
interface PaymentTokenInterfaceFactory
// @codingStandardsIgnoreEnd
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
