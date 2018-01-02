<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Api;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;

/**
 * Remove StockSourceLink list API
 *
 * @api
 */
interface StockSourceLinksDeleteInterface
{
    /**
     * Remove StockSourceLink list
     *
     * @param StockSourceLinkInterface[] $links
     * @return void
     * @throws CouldNotDeleteException
     */
    public function execute(array $links);
}
