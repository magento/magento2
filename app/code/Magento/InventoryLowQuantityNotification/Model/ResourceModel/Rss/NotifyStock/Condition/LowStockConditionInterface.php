<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model\ResourceModel\Rss\NotifyStock\Condition;

interface LowStockConditionInterface
{
    /**
     * @return string
     */
    public function execute(): string;
}
