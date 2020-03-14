<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

use Magento\Quote\Api\Data\Reorder\ReorderOutput;

/**
 * Allows customer to quickly reorder previously added products and put them to the Cart
 */
interface ReorderInterface
{
    /**
     * @param string $incrementOrderId
     * @param string $storeId
     * @return ReorderOutput
     */
    public function execute(string $incrementOrderId, string $storeId): ReorderOutput;
}
