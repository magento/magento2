<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Api;

use Magento\Vault\Model\VaultPaymentInterface;

/**
 * Contains methods to retrieve vault payment methods
 * This interface is consistent with \Magento\Payment\Api\PaymentMethodListInterface
 * @api
 * @since 100.2.0
 */
interface PaymentMethodListInterface
{
    /**
     * Get list of available vault payments
     * @param int $storeId
     * @return VaultPaymentInterface[]
     * @since 100.2.0
     */
    public function getList($storeId);

    /**
     * Get list of enabled in the configuration vault payments
     * @param int $storeId
     * @return VaultPaymentInterface[]
     * @since 100.2.0
     */
    public function getActiveList($storeId);
}
