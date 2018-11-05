<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelection\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\InventorySourceSelectionApi\Model\DistanceProviderPool;

class DistanceProvider implements ArrayInterface
{
    /**
     * @var DistanceProviderPool
     */
    private $distanceProviderPool;

    /**
     * @var array|string[]
     */
    private $distanceProviderDescriptions;

    /**
     * DistanceProvider constructor.
     *
     * @param DistanceProviderPool $distanceProviderPool
     * @param string[] $distanceProviderDescriptions
     */
    public function __construct(
        DistanceProviderPool $distanceProviderPool,
        array $distanceProviderDescriptions = []
    ) {
        $this->distanceProviderPool = $distanceProviderPool;
        $this->distanceProviderDescriptions = $distanceProviderDescriptions;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $distanceProviderCodes = array_keys($this->distanceProviderPool->getList());

        $res = [];
        foreach ($distanceProviderCodes as $distanceProviderCode) {
            $res [] = [
                'value' => $distanceProviderCode,
                'label' => $this->distanceProviderDescriptions[$distanceProviderCode] ?? $distanceProviderCode
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
