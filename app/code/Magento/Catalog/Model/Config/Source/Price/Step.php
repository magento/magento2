<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Config\Source\Price;

use Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory;
use Magento\Framework\Option\ArrayInterface;

class Step implements ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => AlgorithmFactory::RANGE_CALCULATION_AUTO,
                'label' => __('Automatic (equalize price ranges)'),
            ],
            [
                'value' => AlgorithmFactory::RANGE_CALCULATION_IMPROVED,
                'label' => __('Automatic (equalize product counts)')
            ],
            [
                'value' => AlgorithmFactory::RANGE_CALCULATION_MANUAL,
                'label' => __('Manual')
            ]
        ];
    }
}
