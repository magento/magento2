<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Config\Source\Price;

use Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class \Magento\Catalog\Model\Config\Source\Price\Step
 *
 * @since 2.0.0
 */
class Step implements ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     * @since 2.0.0
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
