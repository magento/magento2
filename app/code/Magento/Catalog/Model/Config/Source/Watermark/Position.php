<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Watermark position config source model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Config\Source\Watermark;

/**
 * Class \Magento\Catalog\Model\Config\Source\Watermark\Position
 *
 * @since 2.0.0
 */
class Position implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Get available options
     *
     * @codeCoverageIgnore
     * @return array
     * @since 2.0.0
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
