<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\ResourceModel\IsSalableCondition;

use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Source Item status condition
 */
class GetSourceItemStatusCondition implements GetIsSalableConditionInterface
{
    /**
     * @inheritdoc
     */
    public function execute(): string
    {
        return '(source_item.' . SourceItemInterface::STATUS . ' = ' . SourceItemInterface::STATUS_OUT_OF_STOCK . ')';
    }
}
