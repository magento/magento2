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
            ['label' => __('Publishing'), 'value' => Config::PUBLISHING_STRATEGY],
            ['label' => __('Dynamic'), 'value' => Config::DYNAMIC_STRATEGY]
        ];
    }
}
