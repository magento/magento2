<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
            ['label' => __('None (Translation is disabled)'), 'value' => Config::NO_TRANSLATION],
            ['label' => __('Dictionary (Translation on frontend side)'), 'value' => Config::DICTIONARY_STRATEGY],
            ['label' => __('Embedded (Translation on backend side)'), 'value' => Config::EMBEDDED_STRATEGY]
        ];
    }
}
