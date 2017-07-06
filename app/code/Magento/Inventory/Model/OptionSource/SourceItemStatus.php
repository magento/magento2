<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class SourceItemStatus
 *
 * @api
 */
class SourceItemStatus implements OptionSourceInterface
{
    /**#@+
     * Source items status values
     */
    const SOURCE_ITEM_STATUS_OUT_OF_STOCK = 0;
    const SOURCE_ITEM_STATUS_IN_STOCK = 1;
    /**#@-*/

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::SOURCE_ITEM_STATUS_IN_STOCK,
                'label' => __('In Stock'),
            ],
            [
                'value' => self::SOURCE_ITEM_STATUS_OUT_OF_STOCK,
                'label' => __('Out of Stock'),
            ],
        ];
    }
}
