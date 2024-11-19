<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Model\Js\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Translation\Model\Js\Config;

class Strategy implements ArrayInterface
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
