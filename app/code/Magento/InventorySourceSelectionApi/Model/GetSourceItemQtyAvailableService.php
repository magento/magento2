<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Model;

use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Get source item qty available for usage in SSA
 * Default implementation that returns source item qty without any modifications
 */
class GetSourceItemQtyAvailableService implements GetSourceItemQtyAvailableInterface
{
    /**
     * @inheritDoc
     */
    public function execute(SourceItemInterface $sourceItem): float
    {
        return $sourceItem->getQuantity();
    }
}
