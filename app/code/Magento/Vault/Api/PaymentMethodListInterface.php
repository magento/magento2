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
 * @since 2.1.3
 */
interface PaymentMethodListInterface
{
    /**
     * Get list of available vault payments
     * @param int $storeId
     * @return VaultPaymentInterface[]
     * @since 2.1.3
     */
    public function getList($storeId);

    /**
     * Get list of enabled in the configuration vault payments
     * @param int $storeId
     * @return VaultPaymentInterface[]
     * @since 2.1.3
     */
    public function getActiveList($storeId);
}
