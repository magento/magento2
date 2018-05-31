<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogAdminUi\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Provide option values for UI
 */
class SourceItemStatus implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => SourceItemInterface::STATUS_IN_STOCK,
                'label' => __('In Stock'),
            ],
            [
                'value' => SourceItemInterface::STATUS_OUT_OF_STOCK,
                'label' => __('Out of Stock'),
            ],
        ];
    }
}
