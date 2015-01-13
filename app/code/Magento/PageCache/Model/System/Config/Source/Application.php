<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Caching Application config value selection
 */
namespace Magento\PageCache\Model\System\Config\Source;

/**
 * Class Application
 *
 */
class Application implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => \Magento\PageCache\Model\Config::BUILT_IN, 'label' => __('Built-in Application')],
            ['value' => \Magento\PageCache\Model\Config::VARNISH, 'label' => __('Varnish Caching')]
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
            \Magento\PageCache\Model\Config::BUILT_IN => __('Built-in Application'),
            \Magento\PageCache\Model\Config::VARNISH => __('Varnish Caching')
        ];
    }
}
