<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\System\Config\Source;

/**
 * Downloadable Content Disposition Source
 */
class Contentdisposition implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'attachment', 'label' => __('attachment')],
            ['value' => 'inline', 'label' => __('inline')]
        ];
    }
}
