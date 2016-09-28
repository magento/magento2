<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model;

interface VaultManagementInterface
{
    /**
     * Get list of active vault payment methods
     * @param $storeId
     * @return VaultPaymentInterface[]
     */
    public function getActivePaymentList($storeId);
}
