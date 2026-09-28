<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Translation\Model\Js\Config\Source;

use Magento\Translation\Model\Js\Config;

class Strategy implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Dictionary (Translation on Storefront side)'), 'value' => Config::DICTIONARY_STRATEGY],
            ['label' => __('Embedded (Translation on Admin side)'), 'value' => Config::EMBEDDED_STRATEGY]
        ];
    }
}
