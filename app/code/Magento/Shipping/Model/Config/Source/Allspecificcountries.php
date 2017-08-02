<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model\Config\Source;

/**
 * Class \Magento\Shipping\Model\Config\Source\Allspecificcountries
 *
 * @since 2.0.0
 */
class Allspecificcountries implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('All Allowed Countries')],
            ['value' => 1, 'label' => __('Specific Countries')]
        ];
    }
}
