<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * Class SourceItemStatus
 *
 * @api
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
                'value' => SourceInterface::SOURCE_ITEM_STATUS_IN_STOCK,
                'label' => __('In Stock'),
            ],
            [
                'value' => SourceInterface::SOURCE_ITEM_STATUS_OUT_OF_STOCK,
                'label' => __('Out of Stock'),
            ],
        ];
    }
}
