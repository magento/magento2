<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelectionAdminUi\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class DistanceProvider implements ArrayInterface
{
    /**
     * @var array|string[]
     */
    private $distanceProviderDescriptions;

    /**
     * DistanceProvider constructor.
     *
     * @param string[] $distanceProviderDescriptions
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        array $distanceProviderDescriptions = []
    ) {
        $this->distanceProviderDescriptions = $distanceProviderDescriptions;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $res = [];
        foreach ($this->distanceProviderDescriptions as $code => $description) {
            $res [] = [
                'value' => $code,
                'label' => $description
            ];
        }

        return $res;
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
