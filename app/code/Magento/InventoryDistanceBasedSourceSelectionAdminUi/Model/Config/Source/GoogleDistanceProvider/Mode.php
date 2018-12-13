<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelectionAdminUi\Model\Config\Source\GoogleDistanceProvider;

use Magento\Framework\Option\ArrayInterface;

class Mode implements ArrayInterface
{
    private const MODE_DRIVING = 'driving';
    private const MODE_WALKING = 'walking';
    private const MODE_BICYCLING = 'bicycling';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::MODE_DRIVING, 'label' => __('Driving')],
            ['value' => self::MODE_WALKING, 'label' => __('Walking')],
            ['value' => self::MODE_BICYCLING, 'label' => __('Bicycling')]
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
