<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Model;

/**
 * This class updates the sales channel link website code
 */
interface UpdateSalesChannelWebsiteCodeInterface
{
    /**
     * @param string $oldCode
     * @param string $newCode
     * @return void
     */
    public function execute(string $oldCode, string $newCode);
}
