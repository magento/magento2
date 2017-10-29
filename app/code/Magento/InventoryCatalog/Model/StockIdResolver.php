<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

/**
 * Basic implementation will return same stockId for every case.
 * {@inheritdoc}
 */
class StockIdResolver implements StockIdResolverInterface
{
    /**
     * @inheritdoc
     */
    public function execute(int $stockId): int
    {
        return $stockId;
    }
}
