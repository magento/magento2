<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Caching Application config value selection
 */
namespace Magento\PageCache\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\PageCache\Model\Config;

/**
 * Class Application
 */
class Application implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Config::BUILT_IN,
                'label' => __('Built-in Cache')
            ],
            [
                'value' => Config::VARNISH,
                'label' => __('Varnish Cache (Recommended)')
            ]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            Config::BUILT_IN => __('Built-in Cache'),
            Config::VARNISH => __('Varnish Cache (Recommended)')
        ];
    }
}
