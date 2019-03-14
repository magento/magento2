<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelectionAdminUi\Model\Config\Source\GoogleDistanceProvider;

use Magento\Framework\Option\ArrayInterface;

class Value implements ArrayInterface
{
    private const MODE_DISTANCE = 'distance';
    private const MODE_TIME = 'time';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::MODE_DISTANCE, 'label' => __('Distance')],
            ['value' => self::MODE_TIME, 'label' => __('Time to destination')],
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $options = $this->toOptionArray();
        $return = [];

        foreach ($options as $option) {
            $return[$option['value']] = $option['label'];
        }

        return $return;
    }
}
