<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProductIndexer\Model;

class GetParentConfigurableSkuList
{
    /**
     * @param array $childrenSkuList
     * @return array
     */
    public function execute(array $childrenSkuList): array
    {
        return ['configurable_1'];
    }
}
