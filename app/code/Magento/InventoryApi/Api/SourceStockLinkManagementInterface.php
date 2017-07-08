<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Api;

/**
 * @api
 */
interface SourceStockLinkManagementInterface
{
    /**
     * Get sources assigned to stock
     *
     * @param int $stockId
     * @return \Magento\InventoryApi\Api\Data\SourceStockLinkInterface[]
     */
    public function getAssignedSources($stockId);
}
