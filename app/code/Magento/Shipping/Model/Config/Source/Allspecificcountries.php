<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Shipping\Model\Config\Source;

class Allspecificcountries implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('All Allowed Countries')],
            ['value' => 1, 'label' => __('Specific Countries')]
        ];
    }
}
