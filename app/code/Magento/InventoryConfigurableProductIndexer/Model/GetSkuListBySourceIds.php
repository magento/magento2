<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProductIndexer\Model;

class GetSkuListBySourceIds
{
    /**
     * @param array $sourceItemIds
     * @return array
     */
    public function execute(array $sourceItemIds): array
    {
        return ['simple_11', 'simple_21', 'simple_31'];
    }
}
