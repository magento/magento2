<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Watermark position config source model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Config\Source\Watermark;

class Position implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Get available options
     *
     * @codeCoverageIgnore
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'stretch', 'label' => __('Stretch')],
            ['value' => 'tile', 'label' => __('Tile')],
            ['value' => 'top-left', 'label' => __('Top/Left')],
            ['value' => 'top-right', 'label' => __('Top/Right')],
            ['value' => 'bottom-left', 'label' => __('Bottom/Left')],
            ['value' => 'bottom-right', 'label' => __('Bottom/Right')],
            ['value' => 'center', 'label' => __('Center')]
        ];
    }
}
